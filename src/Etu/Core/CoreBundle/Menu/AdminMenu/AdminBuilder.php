<?php

namespace Etu\Core\CoreBundle\Menu\AdminMenu;

use Etu\Core\CoreBundle\Menu\Sidebar\SidebarBuilder;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Admin menu.
 */
class AdminBuilder extends SidebarBuilder
{
	/**
	 * @param Router $router
	 */
	public function __construct(Router $router)
	{
		$this->blocks = array();
		$this->lastPosition = 0;

		$this
			->addBlock('base.admin_menu.title')
				->add('base.admin_menu.items.dashboard')
					->setIcon('etu-icon-gear')
					->setUrl($router->generate('admin_index'))
				->end()
				->add('base.admin_menu.items.stats')
					->setIcon('etu-icon-chart')
					->setUrl($router->generate('admin_stats'))
				->end()
			->end()
		;
	}
}
