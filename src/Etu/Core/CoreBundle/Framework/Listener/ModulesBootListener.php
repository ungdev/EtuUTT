<?php

namespace Etu\Core\CoreBundle\Framework\Listener;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Module\ModulesManager;
use Etu\Core\CoreBundle\Framework\Twig\GlobalAccessorObject;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;

class ModulesBootListener
{
    /**
     * @var ModulesManager
     */
    protected $modulesManager;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var GlobalAccessorObject
     */
    protected $globalAccessorObject;

    /**
     * @var Container
     */
    protected $container;


    public function __construct(
        Router $router,
        ModulesManager $modulesManager,
        GlobalAccessorObject $globalAccessorObject,
        Container $container
    ) {
        $this->modulesManager = $modulesManager;
        $this->router = $router;
        $this->globalAccessorObject = $globalAccessorObject;
        $this->container = $container;

        $this->container->get('kernel')->freezePermissions();
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $modules = $this->modulesManager->getEnabledModules();
        if (is_array($modules)) {
            // Legacy hack for former doctrine ORM versions.
            $arrayobject = new \ArrayObject($modules);
            $modules = $arrayobject->getIterator();
        }

        // Boot modules
        /** @var $module Module */
        /** @var $modules \Iterator<Module> */
        foreach ($modules as $module) {
            if ($module->mustBoot()) {
                $module->setContainer($this->container);
                $module->setRouter($this->router);
                $module->onModuleBoot();
                $module->setEnabled(true);
            }
        }

        // Give an access from Twig
        $this->globalAccessorObject->set('modules', $this->modulesManager->getModules());

        // Access to env from Twig
        $this->globalAccessorObject->set('environment', $this->container->get('kernel')->getEnvironment());
    }
}
