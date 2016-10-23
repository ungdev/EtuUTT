<?php

namespace Etu\Core\UserBundle\Model;

/**
 * SemesterManager.
 *
 * Able to find the semester of a given date and the current semester.
 * It consider that semesters changes on January 31th and July 31th, so
 * don't use this manager for holydays: it won't work correctly.
 */
class SemesterManager
{
    const SPRING = 'P';
    const AUTUMN = 'A';

    const FIRST_DAY_SPRING = 31;
    const FIRST_DAY_AUTUMN = 212;

    /**
     * @param \DateTime $datetime
     *
     * @return Semester
     */
    public static function find(\DateTime $datetime)
    {
        $dayInYear = $datetime->format('z');

        if ($dayInYear > self::FIRST_DAY_SPRING && $dayInYear < self::FIRST_DAY_AUTUMN) {
            $semester = self::SPRING;
        } else {
            $semester = self::AUTUMN;
        }

        return new Semester($semester, $datetime->format('Y'));
    }

    /**
     * @return Semester
     */
    public static function current()
    {
        $dayInYear = date('z');

        if ($dayInYear > self::FIRST_DAY_SPRING && $dayInYear < self::FIRST_DAY_AUTUMN) {
            $semester = self::SPRING;
        } else {
            $semester = self::AUTUMN;
        }

        return new Semester($semester, date('Y'));
    }
}
