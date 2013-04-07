<?php

namespace Etu\Core\CoreBundle\Notification;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\UserBundle\Entity\User;

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
	 * Send a notification to all the subscribers of given entity
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

		$this->sendTo($subscriptions, $notification);

		return true;
	}

	/**
	 * Send a notification to the given users
	 *
	 * @param User[]|Subscription[] $users
	 * @param Notification $notification
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function sendTo(array $users, Notification $notification)
	{
		foreach ($users as $key => $user) {
			if ($user instanceof Subscription) {
				$users[$key] = $user->getUser();
			} elseif (! $user instanceof User) {
				if (is_object($user)) {
					$type = get_class($user);
				} else {
					$type = gettype($user);
				}

				throw new \InvalidArgumentException(sprintf(
					'NotificationSender::sendTo() is only able to send notifications to users
					(User or Subscription instances, %s given for key %s)', $type, $key
				));
			}
		}

		/** @var $em EntityManager */
		$em = $this->doctrine->getManager();

		// Send it to all the subscribers
		foreach ($users as $user) {
			$notif = new Notification();
			$notif->setUser($user);

			$notif->setHelper($notification->getHelper());
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