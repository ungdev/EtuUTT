<?php

namespace Etu\Core\CoreBundle\Framework\Definition;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

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
	 * @return \Etu\Core\CoreBundle\Menu\AdminMenu\AdminBuilder
	 */
	public function getAdminMenuBuilder()
	{
		return $this->get('etu.menu.admin_builder');
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
			'message' => 'user.auth.denied'
		));

		return $this->redirect($this->generateUrl('user_connect'));
	}

	/**
	 * @return \Etu\Core\UserBundle\Security\Layer\UserLayer
	 */
	public function getUserLayer()
	{
		return new \Etu\Core\UserBundle\Security\Layer\UserLayer($this->getUser());
	}

	/**
	 * Get a user from the Security Context
	 *
	 * @return User|Organization
	 *
	 * @throws \LogicException If SecurityBundle is not available
	 *
	 * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
	 */
	public function getUser()
	{
		return parent::getUser();
	}

	/**
	 * @return \Etu\Core\CoreBundle\Framework\Definition\Module
	 */
	public function getCurrentBundle()
	{
		$bundles = $this->getKernel()->getBundles();
		$currentShortName = $this->getRequest()->attributes->get('_template')->get('bundle');

		return (isset($bundles[$currentShortName])) ? $bundles[$currentShortName] : false;
	}
}
