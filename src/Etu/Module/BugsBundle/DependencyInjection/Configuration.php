<?php

namespace Etu\Module\BugsBundle\DependencyInjection;

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
	    $rootNode = $treeBuilder->root('etu_bugs');
		return $treeBuilder;
    }
}
