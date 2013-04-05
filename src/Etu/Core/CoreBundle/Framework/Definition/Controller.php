<?php

namespace Etu\Core\CoreBundle\Framework\Definition;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Controller extends BaseController
{
	/**
	 * @return \Etu\Core\CoreBundle\Menu\Sidebar\SidebarBuilder
	 */
	public function getSidebarBuilder()
	{
		return $this->get('etu.menu.sidebar_builder');
	}

	/**
	 * @return \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder
	 */
	public function getUserMenuBuilder()
	{
		return $this->get('etu.menu.user_builder');
	}

	/**
	 * @return \Etu\Core\CoreBundle\Framework\EtuKernel
	 */
	public function getKernel()
	{
		return $this->get('kernel');
	}

	/**
	 * @return \Etu\Core\CoreBundle\Notification\SubscriptionsManager
	 */
	public function getSubscriptionsManager()
	{
		return $this->get('etu.notifs.subscriber');
	}

	/**
	 * @return \Etu\Core\CoreBundle\Notification\NotificationSender
	 */
	public function getNotificationsSender()
	{
		return $this->get('etu.notifs.sender');
	}

	/**
	 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
	 */
	public function createAccessDeniedResponse()
	{
		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'error',
			'message' => 'user.auth.needAuth'
		));

		return $this->redirect($this->generateUrl('user_connect'));
	}
}
