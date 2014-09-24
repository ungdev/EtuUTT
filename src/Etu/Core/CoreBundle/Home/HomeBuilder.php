<?php

namespace Etu\Core\CoreBundle\Home;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Twig\GlobalAccessorObject;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\ArgentiqueBundle\Entity\Photo;
use Etu\Module\EventsBundle\Entity\Event;
use Etu\Module\UVBundle\Entity\Review;
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
     * @var GlobalAccessorObject
     */
    protected $globalAccessorObject;

    /**
     * @param EntityManager $manager
     * @param SecurityContext $context
     * @param GlobalAccessorObject $globalAccessorObject
     */
    public function __construct(EntityManager $manager, SecurityContext $context, GlobalAccessorObject $globalAccessorObject)
    {
        $this->manager = $manager;
        $this->user = $context->getToken()->getUser();
        $this->globalAccessorObject = $globalAccessorObject;
    }

    /**
     * @return Course[]
     */
    public function getNextCourses()
    {
        return $this->manager
            ->getRepository('EtuUserBundle:Course')
            ->getUserNextCourses($this->user);
    }

    /**
     * @param Module[] $enabledModules
     * @return \Etu\Core\CoreBundle\Entity\Notification[]
     */
    public function getNotifications($enabledModules)
    {
        $query = $this->manager->createQueryBuilder()
            ->select('n')
            ->from('EtuCoreBundle:Notification', 'n')
            ->where('n.authorId != :userId')
            ->setParameter('userId', $this->user->getId())
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults(25);

        /** @var $subscriptions Subscription[] */
        $subscriptions = $this->globalAccessorObject->get('notifs')->get('subscriptions');
        $subscriptionsWhere = [];

        /** @var $notifications Notification[] */
        $notifications = [];

        if (! empty($subscriptions)) {

            foreach ($subscriptions as $key => $subscription) {
                $subscriptionsWhere[] = '(n.entityType = :type_'.$key.' AND n.entityId = :id_'.$key.')';

                $query->setParameter('type_'.$key, $subscription->getEntityType());
                $query->setParameter('id_'.$key, $subscription->getEntityId());
            }

            if (! empty($subscriptionsWhere)) {
                $query = $query->andWhere(implode(' OR ', $subscriptionsWhere));
            }

            /*
             * Modules
             */
            $modulesWhere = array('n.module = \'core\'', 'n.module = \'user\'');

            foreach ($enabledModules as $module) {
                $identifier = $module->getIdentifier();
                $modulesWhere[] = 'n.module = :module_'.$identifier;

                $query->setParameter('module_'.$identifier, $identifier);
            }

            if (! empty($modulesWhere)) {
                $query = $query->andWhere(implode(' OR ', $modulesWhere));
            }

            // Query
            $notifications = $query->getQuery()->getResult();
        }

        return $notifications;
    }

    /**
     * @return Review[]
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
     * @return Event[]
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

    /**
     * @return Photo[]
     */
    public function getPhotos()
    {
        $query = $this->manager->createQueryBuilder()
            ->select('p')
            ->from('EtuModuleArgentiqueBundle:Photo', 'p')
            ->orderBy('p.createdAt', 'DESC')
            ->where('p.ready = 1')
            ->setMaxResults(6)
            ->getQuery();

        $query->useResultCache(true, 1200);

        return $query->getResult();
    }
}