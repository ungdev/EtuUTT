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
	 * @var \Twig_Environment
	 */
	protected $twig;

	/**
	 * @param SubscriptionsManager $manager
	 * @param \Twig_Environment    $twig
	 */
	public function __construct(SubscriptionsManager $manager, \Twig_Environment $twig)
	{
		$this->manager = $manager;
		$this->twig = $twig;
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
			'render_subscribe_button' => new \Twig_Function_Method($this, 'renderButton', array('is_safe' => array('html'))),
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

	/**
	 * @param $entityType
	 * @param $entityId
	 * @return bool
	 */
	public function renderButton($entityType, $entityId)
	{
		return $this->twig->render('EtuCoreBundle:Subscriptions:button.html.twig', array(
			'entityType' => $entityType,
			'entityId' => $entityId,
		));
	}
}
