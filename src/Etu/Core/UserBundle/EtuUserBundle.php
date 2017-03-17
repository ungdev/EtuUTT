<?php

namespace Etu\Core\UserBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\UserBundle\DependencyInjection\Security\Factory\CasFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EtuUserBundle extends Module
{
    /**
     * @var bool
     */
    protected $enabled = true;

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new CasFactory());
    }

    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'users';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Utilisateurs';
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
        return 'Gestion des utilisateurs, nécessaire au reste du site';
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return [];
    }
}
