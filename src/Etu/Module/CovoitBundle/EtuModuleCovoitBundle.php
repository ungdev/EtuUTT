<?php

namespace Etu\Module\CovoitBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleCovoitBundle extends Module
{
    /**
     * At module boot, update the sidebar.
     */
    public function onModuleBoot()
    {
        $this->getSidebarBuilder()
            ->getBlock('base.sidebar.services.title')
                ->add('covoit.index.title')
                ->setIcon('car.png')
                ->setUrl($this->getRouter()->generate('covoiturage_index'))
                ->setRole('ROLE_COVOIT')
            ->end();
    }

    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'covoit';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Covoit';
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
        return 'Trouver un covoiturage et louer sa voiure';
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
