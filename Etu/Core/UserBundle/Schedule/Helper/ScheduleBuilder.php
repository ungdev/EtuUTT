<?php

namespace Etu\Core\UserBundle\Schedule\Helper;

use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Schedule\Model\CourseHalf;

/**
 * Schedule builder using courses.
 */
class ScheduleBuilder
{
    public const DO_NOT_USE_HALF = false;

    public static $colors = [];

    /**
     * @var Course[]
     */
    protected $courses;

    /**
     * Use half courses ?
     *
     * @var bool
     */
    protected $useHalf;

    /**
     * Constructor.
     *
     * @param mixed $useHalf
     */
    public function __construct($useHalf = true)
    {
        $this->useHalf = (bool) $useHalf;

        $days = [
            Course::DAY_MONDAY, Course::DAY_TUESDAY, Course::DAY_WENESDAY,
            Course::DAY_THURSDAY, Course::DAY_FRIDAY, Course::DAY_SATHURDAY,
        ];

        $hours = ['08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19'];

        foreach ($days as $day) {
            $this->courses[$day] = [];

            foreach ($hours as $hour) {
                $this->courses[$day][$hour.':00'] = ['type' => 'void', 'size' => 1];
                $this->courses[$day][$hour.':30'] = ['type' => 'void', 'size' => 1];
            }
        }
    }

    /**
     * @return $this
     */
    public function addCourse(Course $course)
    {
        if ($this->useHalf) {
            if ('T' == $course->getWeek()) {
                $this->courses[$course->getDay()][$course->getStart()] = [
                    'type' => 'course',
                    'size' => self::getBlockSize($course),
                    'course' => $course,
                ];
            } else {
                if (!isset($this->courses[$course->getDay()][$course->getStart()]['courses'])) {
                    $this->courses[$course->getDay()][$course->getStart()] = [
                        'type' => 'course_half',
                        'size' => self::getBlockSize($course),
                        'courses' => new CourseHalf(),
                    ];
                }

                $this->courses[$course->getDay()][$course->getStart()]['courses']->addCourse($course);
            }
        } else {
            $this->courses[$course->getDay()][$course->getStart()] = [
                'type' => 'course',
                'size' => self::getBlockSize($course),
                'course' => $course,
            ];
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
                if ('course' == $course['type'] || 'course_half' == $course['type']) {
                    $deleteCount = $course['size'] - 1;
                } elseif ($deleteCount > 0) {
                    --$deleteCount;
                    unset($this->courses[$day][$key]);
                }
            }
        }

        $courses = [];

        foreach ($this->courses as $day => $dayCourses) {
            foreach ($dayCourses as $key => $course) {
                $courses[$day][(int) str_replace(':', '', $key)] = $course;
                $courses[$day][(int) str_replace(':', '', $key)]['hour'] = $key;
            }
        }

        return $courses;
    }

    /**
     * @return int
     */
    public static function getBlockSize(Course $course)
    {
        $partsStart = explode(':', $course->getStart());
        $partsEnd = explode(':', $course->getEnd());

        $hours = (int) $partsEnd[0] - (int) $partsStart[0];
        $minutes = (int) $partsEnd[1] - (int) $partsStart[1];

        $size = $hours * 2;

        if (30 == $minutes) {
            ++$size;
        } elseif (-30 == $minutes) {
            --$size;
        }

        return $size;
    }
}
