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
	 * Send a notification
	 *
	 * @param Notification $notif
	 * @param bool         $tryCompile
	 * @return bool
	 */
	public function send(Notification $notif, $tryCompile = false)
	{
		/** @var $em EntityManager */
		$em = $this->doctrine->getManager();

		if (! $notif->getIsSuper() && $tryCompile) {
			$oldDate = new \DateTime();
			$oldDate->setTime(date('h') - 1, date('i'), date('s'));

			$oldNotif = $em->createQueryBuilder()
				->select('n')
				->from('EtuCoreBundle:Notification', 'n')
				->where('n.createdAt > :oldDate')
				->andWhere('n.isSuper = 0')
				->andWhere('n.helper = :helper')
				->andWhere('n.entityType = :entityType')
				->andWhere('n.entityId = :entityId')
				->setParameter('oldDate', $oldDate)
				->setParameter('helper', $notif->getHelper())
				->setParameter('entityType', $notif->getEntityType())
				->setParameter('entityId', $notif->getEntityId())
				->setMaxResults(1)
				->getQuery()
				->getOneOrNullResult();

			if ($oldNotif instanceof Notification) {
				$oldNotif->setEntities(array_merge($oldNotif->getEntities(), $notif->getEntities()));
				$oldNotif->setCreatedAt($notif->getDate());

				$em->persist($oldNotif);
			} else {
				$em->persist($notif);
			}
		} else {
			$em->persist($notif);
		}

		$em->flush();

		return true;
	}
}
