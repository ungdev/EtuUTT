<?php

namespace Etu\Core\CoreBundle\Framework\Definition;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

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
}
