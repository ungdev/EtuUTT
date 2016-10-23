<?php

namespace Etu\Module\TVBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleTVBundle extends Module
{
    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'tv';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'TV';
    }

    /**
     * Module author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'Aurélien Labate';
    }

    /**
     * Module description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Module permettant d\'afficher des informations syncronisées d\'EtuUTT sur les télévisions du foyer et du BDE';
    }

    /**
     * Define the modules requirements (the other required modules) using their identifiers.
     *
     * @return array
     */
    public function getRequirements()
    {
        return array(
            // Insert your requirements here
        );
    }
}
