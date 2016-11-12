<?php

namespace Etu\Module\ArgentiqueBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleArgentiqueBundle extends Module
{
    /**
     * At module boot, update the sidebar.
     */
    public function onModuleBoot()
    {
        $this->getSidebarBuilder()
            ->getBlock('base.sidebar.services.title')
                ->add('argentique.sidebar.service')
                    ->setPosition(99)
                    ->setIcon('argentique.png')
                    ->setUrl($this->router->generate('argentique_index'))
                    ->setRole('ROLE_ARGENTIQUE_READ')
                ->end()
            ->end();

        $this->getAdminMenuBuilder()
            ->getBlock('base.admin_menu.title')
                ->add('argentique.admin.menu')
                    ->setIcon('argentique.png')
                    ->setUrl($this->getRouter()->generate('argentique_admin'))
                    ->setPosition(7)
                    ->setRole('ROLE_ARGENTIQUE_ADMIN')
                ->end()
            ->end();
    }

    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'argentique';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Argentique';
    }

    /**
     * Module author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'Titouan Galopin';
    }

    /**
     * Module description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Module de photos pour Argentique';
    }

    /**
     * Define the modules requirements (the other required modules) using their identifiers.
     *
     * @return array
     */
    public function getRequirements()
    {
        return [
            // Insert your requirements here
        ];
    }

    /**
     * @return string
     */
    public static function getPhotosRoot()
    {
        return __DIR__.'/Resources/photos';
    }
}
