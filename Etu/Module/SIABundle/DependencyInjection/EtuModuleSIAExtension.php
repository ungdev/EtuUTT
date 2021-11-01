<?php

namespace Etu\Module\SIABundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EtuModuleSIAExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('sia.ipa.host', $config['ipa']['host']);
        $container->setParameter('sia.ipa.user', $config['ipa']['user']);
        $container->setParameter('sia.ipa.password', $config['ipa']['password']);
        $container->setParameter('sia.ipa.certificat', $config['ipa']['certificat']);
    }
}
