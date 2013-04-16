<?php

/*
 * This file is part of the TgaAudienceBundle package.
 *
 * (c) Titouan Galopin <http://titouangalopin.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tga\AudienceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * TgaAudience configuration
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('tga_audience');

		$rootNode
			->children()
				->integerNode('session_duration')->defaultValue(300)->end()
				->variableNode('disabled_routes')->defaultValue(array())->end()
				->variableNode('environnements')->defaultValue(array('prod'))->end()
			->end();

		return $treeBuilder;
	}
}
