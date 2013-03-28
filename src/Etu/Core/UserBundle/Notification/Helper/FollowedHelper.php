<?php

namespace Etu\Core\UserBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Helper interface
 *
 * An helper is a class that know how to display a given kind of notification
 */
class FollowedHelper implements HelperInterface
{
	/**
	 * @var \Twig_Environment
	 */
	protected $twig;

	/**
	 * @param \Twig_Environment $twig
	 */
	public function __construct(\Twig_Environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'user_followed';
	}

	/**
	 * @param Notification $notification
	 * @return string
	 */
	public function render(Notification $notification)
	{
		return $this->twig->render('EtuUserBundle:Notification:followed.html.twig', array('notif' => $notification));
	}
}