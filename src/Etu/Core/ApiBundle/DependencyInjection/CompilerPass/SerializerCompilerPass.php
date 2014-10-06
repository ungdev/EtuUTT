<?php

namespace Etu\Core\ApiBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SerializerCompilerPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		if (! $container->hasDefinition('etu.serializer.collection')) {
			return;
		}

		/** @var Definition $collection */
		$collection = $container->getDefinition('etu.serializer.collection');

		$encoders = $container->findTaggedServiceIds('etu.serializer_encoder');
		$normalizers = $container->findTaggedServiceIds('etu.serializer_normalizer');

		foreach ($encoders as $id => $attributes) {
			$collection->addMethodCall('addEncoder', array(new Reference($id)));
		}

		foreach ($normalizers as $id => $attributes) {
            $collection->addMethodCall('addNormalizer', array(new Reference($id)));
		}
	}
}
