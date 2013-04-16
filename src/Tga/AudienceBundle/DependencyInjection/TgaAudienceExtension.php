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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * TgaAudience extension for the DI container
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class TgaAudienceExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

	    $container->setParameter('tga_audience.session_duration', $config['session_duration']);
	    $container->setParameter('tga_audience.disabled_routes', $config['disabled_routes']);
	    $container->setParameter('tga_audience.environnements', $config['environnements']);
    }
}
