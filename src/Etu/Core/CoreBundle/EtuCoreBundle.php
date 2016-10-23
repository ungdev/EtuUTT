<?php

namespace Etu\Core\CoreBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Notification\Helper\HelperCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EtuCoreBundle extends Module
{
    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new HelperCompilerPass());
    }

    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'core';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Core';
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
        return 'Système de base d\'EtuUTT, nécessaire au reste du site';
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return array();
    }
}
