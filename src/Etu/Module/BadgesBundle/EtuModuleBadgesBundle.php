<?php

namespace Etu\Module\BadgesBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleBadgesBundle extends Module
{
    /**
     * At module boot, update the sidebar.
     */
    public function onModuleBoot()
    {
        $this->getAdminMenuBuilder()
            ->getBlock('base.admin_menu.title')
            ->add('badges.admin.menu_item')
            ->setIcon('briefcase.png')
            ->setUrl($this->getRouter()->generate('admin_badges_index'))
            ->setPosition(6)
            ->setRole('ROLE_UV_REVIEW_ADMIN')
            ->end();
    }

    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'badges';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Badges';
    }

    /**
     * Module author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'Arnaud Dufour';
    }

    /**
     * Module description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Module to handle badge creation and attribution';
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
}
