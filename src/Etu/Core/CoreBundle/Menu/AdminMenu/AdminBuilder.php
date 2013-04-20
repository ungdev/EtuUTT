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
		parent::__construct($router);

		$this
			->addBlock('base.sidebar.services.title')
				->add('base.sidebar.services.items.uvs')
					->setIcon('etu-icon-briefcase')
					->setUrl('')
				->end()
				->add('base.sidebar.services.items.table')
					->setIcon('etu-icon-table')
					->setUrl('')
				->end()
				->add('base.sidebar.services.items.wiki')
					->setIcon('etu-icon-info')
					->setUrl('')
				->end()
			->end()
		;
	}
}
