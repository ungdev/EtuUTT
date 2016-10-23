<?php

namespace Etu\Core\CoreBundle\Notification;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\UserBundle\Entity\User;

class SubscriptionsManager
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param string    $entityType
     * @param int       $entityId
     * @param User      $user
     * @param \DateTime $date
     *
     * @return bool
     */
    public function subscribe(User $user, $entityType, $entityId, \DateTime $date = null)
    {
        /** @var $em EntityManager */
        $em = $this->doctrine->getManager();

        if (!$this->isSubscriber($user, $entityType, $entityId)) {
            $subscription = new Subscription();
            $subscription->setEntityType($entityType);
            $subscription->setEntityId($entityId);
            $subscription->setUser($user);

            if ($date instanceof \DateTime) {
                $subscription->setCreatedAt($date);
            }

            $em->persist($subscription);
            $em->flush();
        }

        return true;
    }

    /**
     * @param string $entityType
     * @param int    $entityId
     * @param User   $user
     *
     * @return bool
     */
    public function isSubscriber(User $user, $entityType, $entityId)
    {
        /** @var $em EntityManager */
        $em = $this->doctrine->getManager();

        $subscriptionExists = $em
            ->createQueryBuilder()
            ->select('s, u')
            ->from('EtuCoreBundle:Subscription', 's')
            ->leftJoin('s.user', 'u')
            ->andWhere('s.entityId = :entityId')
            ->andWhere('s.entityType = :entityType')
            ->andWhere('s.user = :user')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId)
            ->setParameter('user', $user->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $subscriptionExists && $subscriptionExists instanceof Subscription;
    }

    /**
     * @param string $entityType
     * @param int    $entityId
     * @param User   $user
     *
     * @return bool
     */
    public function unsubscribe(User $user, $entityType, $entityId)
    {
        /** @var $em EntityManager */
        $em = $this->doctrine->getManager();

        $em->createQueryBuilder()
            ->delete('EtuCoreBundle:Subscription', 's')
            ->andWhere('s.entityId = :entityId')
            ->andWhere('s.entityType = :entityType')
            ->andWhere('s.user = :user')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId)
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->execute();

        return true;
    }
}
