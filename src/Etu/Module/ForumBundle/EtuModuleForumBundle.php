<?php

namespace Etu\Module\ForumBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleForumBundle extends Module
{
    /**
     * At module boot, update the sidebar.
     */

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
        return 'forum';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Forum';
    }

    /**
     * Module author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'Lucas Soulier';
    }

    /**
     * Module description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Module de forum';
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
