<?php

namespace Etu\Core\UserBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class CasFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.cas.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('etu.user.cas.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider));

        $listenerId = 'security.authentication.listener.cas.'.$id;
        $container->setDefinition($listenerId, new DefinitionDecorator('etu.user.cas.authentication.listener'));

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'cas';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}
