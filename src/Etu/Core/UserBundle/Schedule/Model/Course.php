<?php

namespace Etu\Core\UserBundle\Schedule\Model;

/**
 * Course.
 */
class Course
{
    const WEEK_ALL = 'T';
    const WEEK_A = 'A';
    const WEEK_B = 'B';

    const DAY_MONDAY = 'monday';
    const DAY_TUESDAY = 'tuesday';
    const DAY_WENESDAY = 'wednesday';
    const DAY_THURSDAY = 'thursday';
    const DAY_FRIDAY = 'friday';
    const DAY_SATHURDAY = 'sathurday';
    const DAY_SUNDAY = 'sunday';

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

        if ($values->weekname == 'T') {
            $this->week = self::WEEK_ALL;
        } elseif ($values->weekname == 'A') {
            $this->week = self::WEEK_A;
        } elseif ($values->weekname == 'B') {
            $this->week = self::WEEK_B;
        }

        if ($values->jour == 'lundi') {
            $this->day = self::DAY_MONDAY;
        } elseif ($values->jour == 'mardi') {
            $this->day = self::DAY_TUESDAY;
        } elseif ($values->jour == 'mercredi') {
            $this->day = self::DAY_WENESDAY;
        } elseif ($values->jour == 'jeudi') {
            $this->day = self::DAY_THURSDAY;
        } elseif ($values->jour == 'vendredi') {
            $this->day = self::DAY_FRIDAY;
        } elseif ($values->jour == 'samedi') {
            $this->day = self::DAY_SATHURDAY;
        } elseif ($values->jour == 'dimanche') {
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

        if ($minutes == 60) {
            ++$hour;
            $minutes = 0;
        }

        return str_pad($hour, 2, '0', STR_PAD_LEFT).':'.str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }
}
