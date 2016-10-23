<?php

namespace Etu\Core\CoreBundle\Notification\Helper;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class HelperCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('etu.notifs.helper_manager')) {
            return;
        }

        $definition = $container->getDefinition('etu.notifs.helper_manager');

        $taggedServices = $container->findTaggedServiceIds('etu.notifs_helper');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('addHelper', array(new Reference($id)));
        }
    }
}
