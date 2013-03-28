<?php

namespace Etu\Core\CoreBundle\Notification;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Entity\Subscription;

class NotificationSender
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
	 * Sen a notification to all the subscribers of given entity
	 *
	 * @param string $entityType
	 * @param string $entityId
	 * @param Notification $notification
	 * @return bool
	 */
	public function sendToSubscribers($entityType, $entityId, Notification $notification)
	{
		/** @var $em EntityManager */
		$em = $this->doctrine->getManager();

		/** @var $subscriptions Subscription[] */
		$subscriptions = $em
			->createQueryBuilder()
			->select('s, u')
			->from('EtuCoreBundle:Subscription', 's')
			->leftJoin('s.user', 'u')
			->where('s.entityType = :entityType')
			->andWhere('s.entityId = :entityId')
			->setParameter('entityType', $entityType)
			->setParameter('entityId', $entityId)
			->getQuery()
			->getResult();

		// Send it to all the subscribers
		foreach ($subscriptions as $subscription) {
			$notif = new Notification();
			$notif->setHelper($notification->getHelper());
			$notif->setUser($notification->getUser());
			$notif->setExpiration($notification->getExpiration());
			$notif->setEntities($notification->getEntities());
			$notif->setModule($notification->getModule());
			$notif->setDate($notification->getDate());
			$notif->setIsNew($notification->getIsNew());
			$notif->setIsSuper($notification->getIsSuper());

			$em->persist($notif);
		}

		$em->flush();

		return true;
	}
}