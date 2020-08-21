<?php

namespace Etu\Core\ApiBundle\Framework\Routing;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

class ApiResourcesRoutingLoader implements LoaderInterface
{
    /**
     * @var \AppKernel
     */
    private $kernel;

    /**
     * @var LoaderResolverInterface
     */
    private $resolver;

    public function __construct(\AppKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param mixed $resource
     * @param null  $type
     *
     * @throws \RuntimeException
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        /*
         * For each core bunle then for each module
         */

        // CoreBundle
        $loader = $this->resolver->resolve('@EtuCoreBundle/Api/Resource/', 'annotation');

        if ($loader) {
            $routes->addCollection($loader->load('@EtuCoreBundle/Api/Resource/', 'annotation'));
        }

        // UserBundle
        $loader = $this->resolver->resolve('@EtuUserBundle/Api/Resource/', 'annotation');

        if ($loader) {
            $routes->addCollection($loader->load('@EtuUserBundle/Api/Resource/', 'annotation'));
        }

        /** @var $module Module */
        foreach ($this->kernel->getModulesDefinitions() as $module) {
            $routing = $module->getApiRouting();
            $loader = $this->resolver->resolve($routing['resource'], $routing['type']);

            if ($loader) {
                $routes->addCollection($loader->load($routing['resource'], $routing['type']));
            }
        }

        return $routes;
    }

    /**
     * @param mixed       $resource
     * @param string|null $type
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return 'api' === $type;
    }

    /**
     * @return \Symfony\Component\Config\Loader\LoaderResolverInterface
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * @return ApiResourcesRoutingLoader
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }
}
