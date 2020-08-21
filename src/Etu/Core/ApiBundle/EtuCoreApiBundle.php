<?php

namespace Etu\Core\ApiBundle;

use Etu\Core\ApiBundle\DependencyInjection\CompilerPass\GrantTypeCompilerPass;
use Etu\Core\ApiBundle\DependencyInjection\CompilerPass\SerializerCompilerPass;
use Etu\Core\CoreBundle\Framework\Definition\Module;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EtuCoreApiBundle extends Module
{
    /**
     * @var bool
     */
    protected $enabled = true;

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SerializerCompilerPass());
        $container->addCompilerPass(new GrantTypeCompilerPass());
    }

    /**
     * Module identifier (to be required by other modules).
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'api';
    }

    /**
     * Module title (describe shortly its aim).
     *
     * @return string
     */
    public function getTitle()
    {
        return 'API';
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
        return 'Base de l\'API, charge les resources des modules externes dynamiquement';
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return [];
    }
}
