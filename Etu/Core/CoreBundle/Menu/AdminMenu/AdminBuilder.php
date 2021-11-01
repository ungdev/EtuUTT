<?php

namespace Etu\Core\CoreBundle\Menu\AdminMenu;

use Etu\Core\CoreBundle\Menu\Sidebar\SidebarBuilder;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Admin menu.
 */
class AdminBuilder extends SidebarBuilder
{
    public function __construct(Router $router)
    {
        $this->blocks = [];
        $this->lastPosition = 0;

        $this
            ->addBlock('base.admin_menu.title')
                ->add('base.admin_menu.items.server')
                    ->setIcon('edit-list.png')
                    ->setUrl($router->generate('admin_server'))
                    ->setRole('ROLE_CORE_ADMIN_SERVER')
                ->end()
                ->add('base.admin_menu.items.modules')
                    ->setIcon('gear.png')
                    ->setUrl($router->generate('admin_modules'))
                    ->setRole('ROLE_CORE_ADMIN_MODULES')
                ->end()
                ->add('base.admin_menu.items.pages')
                    ->setIcon('book.png')
                    ->setUrl($router->generate('admin_pages'))
                    ->setRole('ROLE_CORE_ADMIN_PAGES')
                ->end()
                ->add('base.admin_menu.items.users')
                    ->setIcon('users.png')
                    ->setUrl($router->generate('admin_users_index'))
                    ->setRole('ROLE_CORE_ADMIN_PROFIL')
                ->end()
                ->add('base.admin_menu.items.orgas')
                    ->setIcon('bank.png')
                    ->setUrl($router->generate('admin_orgas_index'))
                    ->setRole('ROLE_CORE_ADMIN_PROFIL')
                ->end()
            ->end();
    }
}
