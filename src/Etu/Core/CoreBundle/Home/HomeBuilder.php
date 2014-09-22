<?php

namespace Etu\Core\CoreBundle\Home;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\EventsBundle\Entity\Event;
use Symfony\Component\Security\Core\SecurityContext;

class HomeBuilder
{
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param EntityManager $manager
     * @param SecurityContext $context
     */
    public function __construct(EntityManager $manager, SecurityContext $context)
    {
        $this->manager = $manager;
        $this->user = $context->getToken()->getUser();
    }

    /**
     * @return array
     */
    public function getNextCourses()
    {
        return $this->manager
            ->getRepository('EtuUserBundle:Course')
            ->getUserNextCourses($this->user);
    }

    /**
     * @return array
     */
    public function getUvReviews()
    {
        $query = $this->manager
            ->getRepository('EtuModuleUVBundle:Review')
            ->createQbReviewOf($this->user->getUvsList())
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery();

        $query->useResultCache(true, 1200);

        return $query->getResult();
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        $query = $this->manager->createQueryBuilder()
            ->select('e, o')
            ->from('EtuModuleEventsBundle:Event', 'e')
            ->leftJoin('e.orga', 'o')
            ->where('e.begin >= :begin')
            ->setParameter('begin', new \DateTime())
            ->orderBy('e.begin', 'ASC')
            ->addOrderBy('e.end', 'ASC')
            ->setMaxResults(3)
            ->getQuery();

        $query->useResultCache(true, 1200);

        return $query->getResult();
    }
}