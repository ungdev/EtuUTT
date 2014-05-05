<?php

namespace Etu\Module\CumulBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\User;
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

        $root = $this->generateUrl('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $allLogins = $em->getRepository('EtuUserBundle:User')
            ->createQueryBuilder('u')
            ->select('u.login, u.fullName, u.avatar')
            ->where('u.isStudent = 1')
            ->getQuery()
            ->getScalarResult();

        foreach ($allLogins as $key => $allLogin) {
            unset($allLogins[$key]);

            $allLogins[$allLogin['login']] = [
                'value' => $allLogin['login'],
                'label' => $allLogin['fullName'],
                'avatar' => $root . 'photos/'.$allLogin['avatar'],
            ];
        }

        $logins = (isset($_GET['q'])) ? explode(':', $_GET['q']) : [];

        if (! empty($logins)) {
            $query = $em->createQueryBuilder()
                ->select('c, u')
                ->from('EtuUserBundle:Course', 'c')
                ->leftJoin('c.user', 'u');

            foreach ($logins as $key => $login) {
                $query->orWhere('u.login = :login'.$key)
                    ->setParameter('login'.$key, $login);
            }

            /** @var $courses Course[] */
            $courses = $query->getQuery()->getResult();

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
                'allLogins' => json_encode($allLogins)
            ];
        } else {
            return [
                'comparating' => false,
                'allLogins' => json_encode($allLogins),
            ];
        }
	}
}
