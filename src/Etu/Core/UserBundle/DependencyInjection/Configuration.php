<?php

namespace Etu\Core\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('etu_user');

        $rootNode
            ->children()
                ->arrayNode('ldap')
                    ->isRequired()
                    ->children()
                        ->scalarNode('host')->isRequired()->end()
                        ->integerNode('port')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('cas')
                    ->isRequired()
                    ->children()
                        ->scalarNode('version')->isRequired()->end()
                        ->scalarNode('host')->isRequired()->end()
                        ->integerNode('port')->isRequired()->end()
                        ->scalarNode('path')->isRequired()->end()
                        ->booleanNode('change_session_id')->isRequired()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
