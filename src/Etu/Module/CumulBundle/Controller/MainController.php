<?php

namespace Etu\Module\CumulBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Schedule\Helper\ScheduleBuilder;

// Import annotations
use Etu\Module\CumulBundle\Schedule\ScheduleComparator;
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

		if (! isset($_GET['q']) || empty($_GET['q'])) {
			return $this->redirect(
				$this->generateUrl('cumul_index').'?q='.$this->getUser()->getLogin()
			);
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

        $allLogins = $em->getRepository('EtuUserBundle:User')
            ->createQueryBuilder('u')
            ->select('u.login, u.fullName')
            ->where('u.isStudent = 1')
            ->getQuery()
            ->getScalarResult();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST') {

			/** @var $user User */
			$user = $em->getRepository('EtuUserBundle:User')->findOneByFullName($request->get('login'));

			$q = $_GET['q'];

			if ($user) {
				$q .= ':'.$user->getLogin();
			}

			return $this->redirect(
				$this->generateUrl('cumul_index').'?q='.$q
			);
		}

        $logins = explode(':', $_GET['q']);

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

		return array(
			'courses' => $courses,
            'comparison' => $comparator->compare(),
			'users' => $users,
			'countUsers' => count($users),
			'colSize' => round(14 / count($users), 2),
            'allLogins' => json_encode($allLogins)
		);
	}
}
