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
	 * @Route("/schedule/{day}", defaults={"day" = "current"}, name="user_schedule")
	 * @Template()
	 */
	public function scheduleAction($day = 'current')
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

		$days = array(
			Course::DAY_MONDAY, Course::DAY_TUESDAY, Course::DAY_WENESDAY,
			Course::DAY_THURSDAY, Course::DAY_FRIDAY, Course::DAY_SATHURDAY
		);

		if (! in_array($day, $days)) {
			if (date('w') == 0) { // Sunday
				$day = Course::DAY_MONDAY;
			} else {
				$day = $days[date('w') - 1];
			}
		}

		return array(
			'courses' => $builder->build(),
			'currentDay' => $day,
			'currentDateDay' => $days[date('w') - 1],
			'currentHour' => (int) date('Hi'),
		);
	}
}
