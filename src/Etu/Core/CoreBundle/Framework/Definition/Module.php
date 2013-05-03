<?php

namespace Etu\Core\CoreBundle\Framework\Definition;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Security\Layer\SessionLayer;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Router;

/**
 * Module class. A module is a bundle with some more informations, as a title,
 * a description, an author and requirements.
 */
abstract class Module extends Bundle
{
	/**
	 * @var boolean
	 */
	protected $enabled = false;

	/**
	 * @var Router
	 */
	protected $router = null;

	/**
	 * Module identifier (to be required by other modules)
	 *
	 * @return string
	 */
	abstract public function getIdentifier();

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	abstract public function getTitle();

	/**
	 * Module description
	 *
	 * @return string
	 */
	abstract public function getDescription();

	/**
	 * Define the modules requirements (the other required modules) using their identifiers
	 *
	 * @return array
	 */
	abstract public function getRequirements();

	/**
	 * Module is ready to use ?
	 *
	 * @return string
	 */
	public function isReadyToUse()
	{
		return true;
	}

	/**
	 * Module author
	 *
	 * @return string
	 */
	public function getAuthor()
	{
		return 'anonymous';
	}

	/**
	 * Check if the module must boot or not using the context
	 *
	 * @return boolean
	 */
	public function mustBoot()
	{
		return true;
	}

	/**
	 * Execute actions on module boot
	 *
	 * @return string
	 */
	public function onModuleBoot() { }

	/**
	 * @return array
	 */
	public function getAvailablePermissions()
	{
		return array();
	}

	/**
	 * Module author
	 *
	 * @return string
	 */
	public function getRouting()
	{
		return array(
			'type' => 'annotation',
			'resource' => '@'.$this->getName().'/Controller/',
		);
	}

	/**
	 * @return \Etu\Core\CoreBundle\Menu\Sidebar\SidebarBuilder
	 */
	public function getSidebarBuilder()
	{
		return $this->container->get('etu.menu.sidebar_builder');
	}

	/**
	 * @return \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder
	 */
	public function getUserMenuBuilder()
	{
		return $this->container->get('etu.menu.user_builder');
	}

	/**
	 * @return \Etu\Core\CoreBundle\Menu\AdminMenu\AdminBuilder
	 */
	public function getAdminMenuBuilder()
	{
		return $this->container->get('etu.menu.admin_builder');
	}

	/**
	 * @return SessionLayer
	 */
	public function getSessionLayer()
	{
		return new SessionLayer($this->container->get('session'));
	}

	/**
	 * @param \Symfony\Component\Routing\Router $router
	 * @return Module
	 */
	public function setRouter(Router $router)
	{
		$this->router = $router;

		return $this;
	}

	/**
	 * @return \Symfony\Component\Routing\Router
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * @return boolean
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @param boolean $enabled
	 * @return Module
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
		return $this;
	}
}
