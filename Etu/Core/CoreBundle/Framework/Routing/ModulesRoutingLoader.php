<?php

namespace Etu\Core\CoreBundle\Framework\Routing;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

class ModulesRoutingLoader implements LoaderInterface
{
    /**
     * @var bool
     */
    private $loaded = false;

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
        if ($this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $routes = new RouteCollection();

        /** @var $module Module */
        foreach ($this->kernel->getModulesDefinitions() as $module) {
            $routing = $module->getRouting();
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
        return 'modules' === $type;
    }

    /**
     * @return \Symfony\Component\Config\Loader\LoaderResolverInterface
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * @return ModulesRoutingLoader
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }
}
