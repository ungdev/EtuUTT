<?php

namespace Etu\Module\UploadBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleUploadBundle extends Module
{
    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'upload';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Upload';
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
        return 'Module permettant aux utilisateurs l\'upload de fichiers';
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
