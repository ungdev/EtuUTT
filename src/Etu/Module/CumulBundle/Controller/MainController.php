<?php

namespace Etu\Module\CumulBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Schedule\Helper\ScheduleBuilder;
use Etu\Module\CumulBundle\Schedule\ScheduleComparator;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/cumul", name="cumul_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isStudent()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

        /*
         * Parse the request, find users, remove invalid logins and find courses
         */
        $logins = (isset($_GET['q']) && ! empty($_GET['q'])) ? explode(':', $_GET['q']) : [];

        if (empty($logins)) {
            return $this->redirect($this->generateUrl('cumul_index') . '?q=' . $this->getUser()->getLogin());
        }

        $qb = $em->createQueryBuilder();

        /** @var User[] $users */
        $users = $qb->select('u')
            ->from('EtuUserBundle:User', 'u')
            ->where($qb->expr()->in('u.login', $logins))
            ->getQuery()
            ->getResult();

        if (count($logins) != count($users)) {
            $found = [];

            foreach ($users as $user) {
                $found[] = $user->getLogin();
            }

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'error',
                'message' => 'Les logins "'. implode('", "', array_diff($logins, $found)) .'" ont été retirés car ils n\'existent pas.'
            ));

            return $this->redirect($this->generateUrl('cumul_index') . '?q=' . implode(':', $found));
        }

        $usersIds = [];

        foreach ($users as $user) {
            $usersIds[] = $user->getId();
        }

        /** @var $courses Course[] */
        $courses = $em->createQueryBuilder()
            ->select('c, u')
            ->from('EtuUserBundle:Course', 'c')
            ->leftJoin('c.user', 'u')
            ->where($qb->expr()->in('u.id', $usersIds))
            ->getQuery()
            ->getResult();

        /*
         * Generate add URLs
         */
        $qb = $em->createQueryBuilder();

        $branchsUsers = $qb->select('u.login, u.branch, u.niveau')
            ->from('EtuUserBundle:User', 'u')
            ->where($qb->expr()->in('u.branch', array_values(User::$branches)))
            ->orderBy('u.branch', 'ASC')
            ->addOrderBy('u.niveau', 'ASC')
            ->getQuery()
            ->getScalarResult();

        $addBranchs = [];

        foreach ($branchsUsers as $branchsUser) {
            $branch = $branchsUser['branch'];

            if (empty($branch)) {
                continue;
            }

            if (! isset($addBranchs[$branch]['all'])) {
                $addBranchs[$branch]['all'] = $logins;
            }

            if (! isset($addBranchs[$branch][$branchsUser['niveau']])) {
                $addBranchs[$branch][$branchsUser['niveau']] = $logins;
            }

            $addBranchs[$branch]['all'][] = $branchsUser['login'];
            $addBranchs[$branch][$branchsUser['niveau']][] = $branchsUser['login'];
        }

        /*
         * Generate remove URLs
         */
        // Logins
        $removeUrlsLogins = [];

        foreach ($logins as $login) {
            $others = $logins;
            unset($others[array_search($login, $logins)]);
            $removeUrlsLogins[$login] = $others;
        }

        // Branchs
        $branchs = [];
        $removeUrlsBranchs = [];

        foreach ($users as $user) {
            $branchs[$user->getBranch()][] = $user->getLogin();
        }

        foreach ($branchs as $branch => $l) {
            if (empty($branch)) {
                $branch = 'Autre';
            }

            $others = $branchs;
            unset($others[$branch]);

            $removeUrlsBranchs[$branch] = [];

            foreach ($others as $branchLogins) {
                foreach ($branchLogins as $branchLogin) {
                    $removeUrlsBranchs[$branch][] = $branchLogin;
                }
            }
        }

        unset($users);

        /*
         * Compare schedules
         */
        /** @var $builders ScheduleBuilder[] */
        $builders = array();

        /** @var $users User[] */
        $users = array();

        foreach ($courses as $course) {
            if (! isset($builders[$course->getUser()->getLogin()])) {
                $builders[$course->getUser()->getLogin()] = new ScheduleBuilder();
            }

            $builders[$course->getUser()->getLogin()]->addCourse($course);
            $users[$course->getUser()->getLogin()] = $course->getUser();
        }

        foreach ($logins as $login) {
            if (! isset($users[$login])) {
                $builders[$login] = new ScheduleBuilder();
                $users[$login] = $em->getRepository('EtuUserBundle:User')->findOneByLogin($login);
            }
        }

        $comparator = new ScheduleComparator($builders);

        return [
            'comparating' => true,
            'courses' => $courses,
            'comparison' => $comparator->compare(),
            'users' => $users,
            'countUsers' => count($users),
            'colSize' => round(14 / count($users), 2),
            'logins' => json_encode($logins),
            'addBranchs' => $addBranchs,
            'removeUrlsLogins' => $removeUrlsLogins,
            'removeUrlsBranchs' => $removeUrlsBranchs,
        ];
	}

    /**
     * @Route("/import/{type}", name="cumul_import")
     * @Template()
     */
    public function importAction(Request $request, $type)
    {
        $post = $request->request;
        $files = $request->files;

        $logins = [];

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if ($type == 'file') {
            /*
             * File import
             */

            // Data
            $data = file_get_contents($files->get('import-file')->getPathname());

            // Data type
            $dataType = $post->get('import-data-type');

            if (! in_array($dataType, ['fullName', 'login', 'studentId'])) {
                $dataType = 'fullName';
            }

            // Separator
            $separator = "\n";

            if ($post->get('separator-textarea') != 'new-line' && strlen($post->get('separator-char')) >= 1) {
                $separator = $post->get('separator-char');
            }

            $dataItems = array_map('trim', explode($separator, $data));
        } else {
            /*
             * Textarea import
             */

            // Data
            $data = $post->get('import-textarea');

            // Data type
            $dataType = $post->get('import-data-type');

            if (! in_array($dataType, ['fullName', 'login', 'studentId'])) {
                $dataType = 'fullName';
            }

            // Separator
            $separator = "\n";

            if ($post->get('separator-textarea') != 'new-line' && strlen($post->get('separator-char')) >= 1) {
                $separator = $post->get('separator-char');
            }

            $dataItems = array_map('trim', explode($separator, $data));
        }

        $qb = $em->createQueryBuilder();

        /** @var User[] $users */
        $users = $qb->select('u')
            ->from('EtuUserBundle:User', 'u')
            ->where($qb->expr()->in('u.'.$dataType, $dataItems))
            ->getQuery()
            ->getArrayResult();

        if (count($dataItems) != count($users)) {
            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'error',
                'message' => (count($dataItems) - count($users)) . ' éléments n\'ont pas pu être ajoutés car ils étaient invalides.'
            ));
        }

        foreach ($users as $user) {
            $logins[] = $user['login'];
        }

        return $this->redirect($this->generateUrl('cumul_index').'?q='.implode(':', $logins));
    }
}
