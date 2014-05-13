<?php

namespace Etu\Module\CovoitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Should not be edited as you are using modules system of EtuUTT.
 */
class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('etu_covoit');
		return $treeBuilder;
	}
}
