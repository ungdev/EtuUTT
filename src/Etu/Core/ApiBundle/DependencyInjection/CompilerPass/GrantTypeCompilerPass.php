<?php

namespace Etu\Core\ApiBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class GrantTypeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (! $container->hasDefinition('etu.oauth.server')) {
            return;
        }

        /** @var Definition $server */
        $server = $container->getDefinition('etu.oauth.server');

        $grantTypes = $container->findTaggedServiceIds('etu.oauth.grant_type');

        foreach ($grantTypes as $id => $attributes) {
            $server->addMethodCall('addGrantType', array(new Reference($id)));
        }
    }
}
