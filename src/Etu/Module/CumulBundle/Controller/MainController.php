<?php

namespace Etu\Module\CumulBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Schedule\Helper\ScheduleBuilder;

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

		if (! isset($_GET['q']) || empty($_GET['q'])) {
			return $this->redirect(
				$this->generateUrl('cumul_index').'?q='.$this->getUser()->getLogin()
			);
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

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

		$query = $em->createQueryBuilder()
			->select('c, u')
			->from('EtuUserBundle:Course', 'c')
			->innerJoin('c.user', 'u');

		$i = 0;

		foreach (explode(':', $_GET['q']) as $key => $login) {
			$query->orWhere('u.login = :login'.$key)
				->setParameter('login'.$key, $login);

			if ($i == 4) {
				break;
			}

			$i++;
		}

		/** @var $courses Course[] */
		$courses = $query->getQuery()->getResult();

		/** @var $builders ScheduleBuilder[] */
		$builders = array();

		/** @var $users User[] */
		$users = array();

		foreach ($courses as $course) {
			if (! isset($builders[$course->getUser()->getLogin()])) {
				$builders[$course->getUser()->getLogin()] = new ScheduleBuilder(ScheduleBuilder::DO_NOT_USE_HALF);
			}

			$builders[$course->getUser()->getLogin()]->addCourse($course);
			$users[$course->getUser()->getLogin()] = $course->getUser();
		}

		foreach ($builders as $key => $builder) {
			$builders[$key] = $builder->build();
		}

		$courses = array();

		foreach ($builders as $login => $items) {
			foreach ($items as $day => $dayCourses) {
				$courses[$day][$login] = $dayCourses;
			}
		}

		$usersRemovedUrl = array();

		foreach ($users as $user) {
			$query = explode(':', $_GET['q']);
			unset($query[array_search($user->getLogin(), $query)]);

			$usersRemovedUrl[$user->getLogin()] = implode(':', $query);
		}

		$letters = array('a', 'b', 'c', 'd', 'e');
		$usersLetters = array();
		$i = 0;

		foreach ($users as $user) {
			$usersLetters[$user->getLogin()] = $letters[$i];
			$i++;
		}

		return array(
			'courses' => $courses,
			'users' => $users,
			'countUsers' => count($users),
			'colSize' => round(14 / count($users), 2),
			'usersLetters' => $usersLetters,
			'usersRemovedUrl' => $usersRemovedUrl,
			'lastUser' => $user
		);
	}
}
