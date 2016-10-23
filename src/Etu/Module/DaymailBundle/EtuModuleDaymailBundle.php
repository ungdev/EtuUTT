<?php

namespace Etu\Module\DaymailBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;

class EtuModuleDaymailBundle extends Module
{
    /**
     * At module boot, update the sidebar.
     */
    public function onModuleBoot()
    {
        /*
        $this->getUserMenuBuilder()
            ->add('base.user.menu.dailymail')
                ->setIcon('megaphone.png')
                ->setUrl($this->router->generate('user_daymail'))
                ->setPosition(4)
            ->end();
        */
    }

    /**
     * @return bool
     */
    public function isReadyToUse()
    {
        return true;
    }

    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'daymail';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Daymail';
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
        return 'Envoi du daymail et interface de préférences de l\'étudiant';
    }

    /**
     * @return array
     */
    public function getAvailablePermissions()
    {
        return array(
            new OrgaPermission('daymail', 'Peut modifier le daymail de l\'association'),
        );
    }

    /**
     * Define the modules requirements (the other required modules) using their identifiers.
     *
     * @return array
     */
    public function getRequirements()
    {
        return array(
            'upload',
        );
    }
}
