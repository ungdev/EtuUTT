<?php

namespace Etu\Module\CumulBundle\Schedule;

use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Schedule\Helper\ScheduleBuilder;

class ScheduleComparator
{
    /**
     * @var ScheduleBuilder
     */
    protected $builders;

    /**
     * @param ScheduleBuilder[] $builders
     */
    public function __construct(array $builders = [])
    {
        $this->builders = $builders;
    }

    /**
     * @param ScheduleBuilder $builder
     *
     * @return $this
     */
    public function addBuilder(ScheduleBuilder $builder)
    {
        $this->builders[] = $builder;

        return $this;
    }

    /**
     * @param ScheduleBuilder $builders
     *
     * @return $this
     */
    public function setBuilders($builders)
    {
        $this->builders = $builders;

        return $this;
    }

    /**
     * @return ScheduleBuilder
     */
    public function getBuilders()
    {
        return $this->builders;
    }

    /**
     * @return array
     */
    public function compare()
    {
        // Free time
        $freeTime = [];

        foreach ($this->builders as $key => $builder) {
            $week = $builder->build();

            foreach ($week as $day => $hours) {
                foreach ($hours as $hour => $course) {
                    if ($course['type'] == 'void') {
                        $freeTime[$key][$day][$hour] = 'T';
                    }
                }
            }
        }

        // Availability
        $avWeek = [];
        $unavWeek = [];

        $days = [
            Course::DAY_MONDAY,
            Course::DAY_TUESDAY,
            Course::DAY_WENESDAY,
            Course::DAY_THURSDAY,
            Course::DAY_FRIDAY,
            Course::DAY_SATHURDAY,
        ];

        $hours = ['08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19'];

        foreach ($days as $day) {
            $avWeek[$day] = [];
            $unavWeek[$day] = [];

            foreach ($hours as $hour) {
                $avWeek[$day][(int) $hour.'00'] = [];
                $avWeek[$day][(int) $hour.'30'] = [];
                $unavWeek[$day][(int) $hour.'00'] = [];
                $unavWeek[$day][(int) $hour.'30'] = [];
            }
        }

        // Comparison
        foreach ($avWeek as $avDay => $avHours) {
            foreach ($avHours as $avHour => $avUsers) {
                foreach ($freeTime as $ftUser => $ftUserWeek) {
                    if (isset($ftUserWeek[$avDay][$avHour])) {
                        $avWeek[$avDay][$avHour][] = $ftUser;
                    } else {
                        $unavWeek[$avDay][$avHour][] = $ftUser;
                    }
                }
            }
        }

        return [$avWeek, $unavWeek];
    }
}
