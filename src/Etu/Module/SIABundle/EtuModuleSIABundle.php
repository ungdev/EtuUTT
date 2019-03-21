<?php

namespace Etu\Module\SIABundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleSIABundle extends Module
{
    /**
     * On module boot, update sidebar.
     *
     * @return string|void
     */
    public function onModuleBoot()
    {
        $this->getSidebarBuilder()
                ->getBlock('base.sidebar.services.title')
                    ->add('Mon compte SIA')
                    ->setIcon('gear.png')
                    ->setUrl($this->getRouter()->generate('sia_index'))
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
        return 'sia';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'SIA';
    }

    /**
     * Module author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'Christian d\'Autume';
    }

    /**
     * Module description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'SIA account module';
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
