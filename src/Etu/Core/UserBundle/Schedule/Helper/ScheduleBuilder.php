<?php

namespace Etu\Core\UserBundle\Schedule\Helper;

use Etu\Core\UserBundle\Entity\Course;

/**
 * Schedule builder using courses.
 */
class ScheduleBuilder
{
	/**
	 * @var Course[]
	 */
	protected $courses;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$days = array(
			Course::DAY_MONDAY, Course::DAY_TUESDAY, Course::DAY_WENESDAY,
			Course::DAY_THURSDAY, Course::DAY_FRIDAY, Course::DAY_SATHURDAY
		);

		$hours = array('08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19');

		foreach ($days as $day) {
			$this->courses[$day] = array();

			foreach ($hours as $hour) {
				$this->courses[$day][$hour.':00'] = array('type' => 'void', 'size' => 1);
				$this->courses[$day][$hour.':30'] = array('type' => 'void', 'size' => 1);
			}
		}
	}

	/**
	 * @param Course $course
	 */
	public function addCourse(Course $course)
	{
		$this->courses[$course->getDay()][$course->getStart()] = array(
			'type' => 'course',
			'size' => $this->getBlockSize($course),
			'course' => $course,
		);
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\Course[]
	 */
	public function build()
	{
		foreach ($this->courses as $day => $dayCourses) {
			$deleteCount = 0;

			foreach ($dayCourses as $key => $course) {
				if ($course['type'] == 'course') {
					$deleteCount = $course['size'] - 1;
				} elseif ($deleteCount > 0) {
					$deleteCount--;
					unset($this->courses[$day][$key]);
				}
			}
		}

		return $this->courses;
	}

	/**
	 * @param Course $course
	 * @return int
	 */
	private function getBlockSize(Course $course)
	{
		$partsStart = explode(':', $course->getStart());
		$partsEnd = explode(':', $course->getEnd());

		$hours = (int) $partsEnd[0] - (int) $partsStart[0];
		$minutes = (int) $partsEnd[1] - (int) $partsStart[1];

		$size = $hours * 2;

		if ($minutes == 30) {
			$size++;
		} elseif ($minutes == -30) {
			$size--;
		}

		return $size;
	}
}
