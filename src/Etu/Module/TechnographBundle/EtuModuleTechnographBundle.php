<?php

namespace Etu\Module\TechnographBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleTechnographBundle extends Module
{

  /**
     * At module boot, update the sidebar.
     */
    public function onModuleBoot()
    {
        $this->getSidebarBuilder()
            ->getBlock('base.sidebar.services.title')
                ->add('technograph.sidebar.service')
                    ->setPosition(100)
                    ->setIcon('argentique.png')
                    ->setUrl($this->router->generate('technograph_index'))
                    ->setRole('ROLE_TECHNOGRAPH_READ')
                ->end()
            ->end();
    }

    /**
     * Module identifier (to be required by other modules)
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'technograph';
    }

    /**
     * Module title (describe shortly its aim)
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Technograph';
    }

    /**
     * Module author
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'Arnaud Dufour';
    }

    /**
     * Module description
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Module to handle Technograph\'s blog';
    }

    /**
     * Define the modules requirements (the other required modules) using their identifiers
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
