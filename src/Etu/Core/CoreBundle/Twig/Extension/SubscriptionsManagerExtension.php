<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

use Etu\Core\CoreBundle\Notification\SubscriptionsManager;
use Etu\Core\UserBundle\Entity\User;

/**
 * SubscriptionsManagerExtension
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class SubscriptionsManagerExtension extends \Twig_Extension
{
	/**
	 * @var SubscriptionsManager
	 */
	protected $manager;

	/**
	 * @param SubscriptionsManager $manager
	 */
	public function __construct(SubscriptionsManager $manager)
	{
		$this->manager = $manager;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'subscriptions_manager';
	}

	/**
	 * @return array
	 */
	public function getFunctions()
	{
		return array(
			// 'notifs_subscribe' => new \Twig_Function_Method($this, 'subscribe'),
			// 'notifs_unsubscribe' => new \Twig_Function_Method($this, 'unsubscribe'),
			'is_subscriber' => new \Twig_Function_Method($this, 'isSubscriber'),
		);
	}

	/**
	 * @param User      $user
	 * @param           $entityType
	 * @param           $entityId
	 * @param \DateTime $date
	 * @return bool
	 */
	public function subscribe(User $user, $entityType, $entityId, \DateTime $date = null)
	{
		return $this->manager->subscribe($user, $entityType, $entityId, $date);
	}

	/**
	 * @param User      $user
	 * @param           $entityType
	 * @param           $entityId
	 * @return bool
	 */
	public function unsubscribe(User $user, $entityType, $entityId)
	{
		return $this->manager->unsubscribe($user, $entityType, $entityId);
	}

	/**
	 * @param User      $user
	 * @param           $entityType
	 * @param           $entityId
	 * @return bool
	 */
	public function isSubscriber(User $user, $entityType, $entityId)
	{
		return $this->manager->isSubscriber($user, $entityType, $entityId);
	}
}
