<?php

use Tga\Api\Framework\HttpKernel\Kernel;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ApiKernel extends Kernel
{
    /**
     * @inheritdoc
     */
    protected function createMap(\Tga\Api\Framework\Resources\Map\MapInterface $map)
    {
        $map
            ->addResourcesLocation('src/Etu/*/*Bundle/Api/Resource')
            ->addModelsLocation('src/Etu/*/*Bundle/Api/Model')
        ;
    }

	/**
	 * Build the container. You should use this method to define custom compiler pass classes.
	 *
	 * @param ContainerBuilder $container
	 * @return void
	 */
	protected function buildContainer(ContainerBuilder $container)
	{
		$loader = new YamlFileLoader($this->container, new FileLocator(__DIR__.'/config'));
		$loader->load('services.yml');
	}
}
