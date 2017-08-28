<?php

namespace Etu\Core\UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EtuUserExtension extends Extension
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

        $container->setParameter('etu.ldap.host', $config['ldap']['host']);
        $container->setParameter('etu.ldap.port', $config['ldap']['port']);

        $container->setParameter('etu.dolibarr.host', $config['dolibarr']['host']);
        $container->setParameter('etu.dolibarr.key', $config['dolibarr']['key']);

        $container->setParameter('etu.cas.version', $config['cas']['version']);
        $container->setParameter('etu.cas.host', $config['cas']['host']);
        $container->setParameter('etu.cas.port', $config['cas']['port']);
        $container->setParameter('etu.cas.path', $config['cas']['path']);
        $container->setParameter('etu.cas.change_session_id', $config['cas']['change_session_id']);
    }
}
