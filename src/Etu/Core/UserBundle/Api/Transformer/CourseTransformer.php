<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\Course;

class CourseTransformer extends AbstractTransformer
{
    /**
     * @param Course $course
     * @param EmbedBag $includes
     * @return array
     */
    public function transformUnique($course, EmbedBag $includes)
    {
        return $this->getData($course);
    }

    /**
     * @param Course $course
     * @return array
     */
    private function getData(Course $course)
    {
        $start = explode(':', $course->getStart());
        $end = explode(':', $course->getEnd());

        return [
            'day' => $course->getDay(),
            'start' => [ 'hour' => (int) $start[0], 'minute' => (int) $start[1] ],
            'end' => [ 'hour' => (int) $end[0], 'minute' => (int) $end[1] ],
            'week' => $course->getWeek(),
            'uv' => $course->getUv(),
            'type' => $course->getType(),
            'room' => $course->getRoom(),
        ];
    }
}