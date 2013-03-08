<?php

namespace Etu\CoreBundle\Framework;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
	/**
	 * @return \Etu\CoreBundle\Menu\Sidebar\SidebarBuilder
	 */
	public function getSidebarBuilder()
	{
		return $this->get('etu.menu.sidebar_builder');
	}

	/**
	 * @return \Etu\CoreBundle\Menu\UserMenu\UserMenuBuilder
	 */
	public function getUserMenuBuilder()
	{
		return $this->get('etu.menu.user_builder');
	}
}
