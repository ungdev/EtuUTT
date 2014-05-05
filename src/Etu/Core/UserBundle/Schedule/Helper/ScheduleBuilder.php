<?php

namespace Etu\Core\UserBundle\Schedule\Helper;

use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Schedule\Model\CourseHalf;

/**
 * Schedule builder using courses.
 */
class ScheduleBuilder
{
	const DO_NOT_USE_HALF = false;

	static $colors = array();

	/**
	 * @var Course[]
	 */
	protected $courses;

	/**
	 * Use half courses ?
	 *
	 * @var boolean
	 */
	protected $useHalf;

	/**
	 * Constructor
	 */
	public function __construct($useHalf = true)
	{
		$this->useHalf = (boolean) $useHalf;

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
	 * @return $this
	 */
	public function addCourse(Course $course)
	{
		if ($this->useHalf) {
			if ($course->getWeek() == 'T') {
				$this->courses[$course->getDay()][$course->getStart()] = array(
					'type' => 'course',
					'size' => self::getBlockSize($course),
					'course' => $course,
				);
			} else {
				if (! isset($this->courses[$course->getDay()][$course->getStart()]['courses'])) {
					$this->courses[$course->getDay()][$course->getStart()] = array(
						'type' => 'course_half',
						'size' => self::getBlockSize($course),
						'courses' => new CourseHalf(),
					);
				}

				$this->courses[$course->getDay()][$course->getStart()]['courses']->addCourse($course);
			}
		} else {
			$this->courses[$course->getDay()][$course->getStart()] = array(
				'type' => 'course',
				'size' => self::getBlockSize($course),
				'course' => $course,
			);
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function build()
	{
		foreach ($this->courses as $day => $dayCourses) {
			$deleteCount = 0;

			foreach ($dayCourses as $key => $course) {
				if ($course['type'] == 'course' || $course['type'] == 'course_half') {
					$deleteCount = $course['size'] - 1;
				} elseif ($deleteCount > 0) {
					$deleteCount--;
					unset($this->courses[$day][$key]);
				}
			}
		}

		$courses = array();

		foreach ($this->courses as $day => $dayCourses) {
			foreach ($dayCourses as $key => $course) {
				$courses[$day][(int) str_replace(':', '', $key)] = $course;
				$courses[$day][(int) str_replace(':', '', $key)]['hour'] = $key;
			}
		}

		return $courses;
	}

	/**
	 * @param Course $course
	 * @return int
	 */
	public static function getBlockSize(Course $course)
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
