<?php

namespace Etu\Module\EventsBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;

class EtuModuleEventsBundle extends Module
{
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
        return 'events';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Evènements';
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
        return 'Affichage et gestion des évènements';
    }

    /**
     * @return array
     */
    public function getAvailablePermissions()
    {
        return [
            new OrgaPermission('events', 'Peut gérer les évènements de l\'association'),
        ];
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
