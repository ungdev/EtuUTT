<?php

namespace Etu\Core\UserBundle\Schedule\Model;

/**
 * Course.
 */
class Course
{
    public const WEEK_ALL = 'T';
    public const WEEK_A = 'A';
    public const WEEK_B = 'B';

    public const DAY_MONDAY = 'monday';
    public const DAY_TUESDAY = 'tuesday';
    public const DAY_WENESDAY = 'wednesday';
    public const DAY_THURSDAY = 'thursday';
    public const DAY_FRIDAY = 'friday';
    public const DAY_SATHURDAY = 'sathurday';
    public const DAY_SUNDAY = 'sunday';

    /**
     * @var string
     */
    protected $uv;

    /**
     * @var int
     */
    protected $studentId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $day;

    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;

    /**
     * @var string
     */
    protected $week;

    /**
     * @var string
     */
    protected $room;

    /**
     * Constructor.
     */
    public function __construct(\stdClass $values)
    {
        $this->uv = $values->uv;
        $this->type = $values->type;
        $this->studentId = $values->etu_id;
        $this->room = $values->code_salle_cru;

        if ('T' == $values->weekname) {
            $this->week = self::WEEK_ALL;
        } elseif ('A' == $values->weekname) {
            $this->week = self::WEEK_A;
        } elseif ('B' == $values->weekname) {
            $this->week = self::WEEK_B;
        }

        if ('lundi' == $values->jour) {
            $this->day = self::DAY_MONDAY;
        } elseif ('mardi' == $values->jour) {
            $this->day = self::DAY_TUESDAY;
        } elseif ('mercredi' == $values->jour) {
            $this->day = self::DAY_WENESDAY;
        } elseif ('jeudi' == $values->jour) {
            $this->day = self::DAY_THURSDAY;
        } elseif ('vendredi' == $values->jour) {
            $this->day = self::DAY_FRIDAY;
        } elseif ('samedi' == $values->jour) {
            $this->day = self::DAY_SATHURDAY;
        } elseif ('dimanche' == $values->jour) {
            $this->day = self::DAY_SUNDAY;
        }

        $this->start = $this->formatHour($values->debut);
        $this->end = $this->formatHour($values->fin);
    }

    /**
     * @return string
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return int
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return string
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getStudentId()
    {
        return $this->studentId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getUv()
    {
        return $this->uv;
    }

    /**
     * @return string
     */
    public function getWeek()
    {
        return $this->week;
    }

    protected function formatHour($hour)
    {
        $parts = explode(':', $hour);
        $hour = (int) $parts[0];
        $minutes = (int) $parts[1];

        if (60 == $minutes) {
            ++$hour;
            $minutes = 0;
        }

        return str_pad($hour, 2, '0', STR_PAD_LEFT).':'.str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }
}
