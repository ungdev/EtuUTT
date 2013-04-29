<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Schedule\Helper\ScheduleBuilder;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ScheduleController extends Controller
{
	/**
	 * @Route("/schedule", name="user_schedule")
	 * @Template()
	 */
	public function scheduleAction()
	{
		if (! $this->getUserLayer()->isStudent()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $myCourses Course[] */
		$courses = $em->getRepository('EtuUserBundle:Course')->findByUser($this->getUser());

		// Builder to create the schedule
		$builder = new ScheduleBuilder();

		foreach ($courses as $course) {
			$builder->addCourse($course);
		}

		return array(
			'courses' => $builder->build(),
			'phoneDay' => (strtolower(date('l')) != 'sunday') ? strtolower(date('l')) : 'monday'
		);
	}
}
