<?php

namespace Etu\Core\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\User;

class CourseRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return Course[]
     */
    public function getUserNextCourses(User $user)
    {
        /** @var Course[] $todayCourses */
        $todayCourses = $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.day = :day')
            ->orderBy('c.start', 'ASC')
            ->setParameter('user', $user->getId())
            ->setParameter('day', Course::getTodayConstant())
            ->getQuery()
            ->getResult();

        $nextCourses = [];

        foreach ($todayCourses as $course) {
            if ($course->getStartAsInt() >= (int) date('Hi') - 15) {
                $nextCourses[$course->getStart()][] = $course;
            }
        }

        return array_slice($nextCourses, 0, 5);
    }
}