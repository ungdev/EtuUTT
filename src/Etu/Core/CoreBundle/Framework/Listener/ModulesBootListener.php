<?php

namespace Etu\Core\CoreBundle\Framework\Listener;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Module\ModulesCollection;
use Etu\Core\CoreBundle\Framework\Module\ModulesManager;
use Etu\Core\CoreBundle\Framework\Twig\GlobalAccessorObject;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContextInterface;

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


	public function __construct(Router $router,
	                            ModulesManager $modulesManager,
	                            GlobalAccessorObject $globalAccessorObject,
	                            Container $container)
	{
		$this->modulesManager = $modulesManager;
		$this->router = $router;
		$this->globalAccessorObject = $globalAccessorObject;
		$this->container = $container;
	}

	/**
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		$modules = $this->modulesManager->getModules();

		// Boot modules
		foreach ($modules as &$module) {
			/** @var $module Module */
			if ($module->mustBoot()) {
				$module->setContainer($this->container);
				$module->setRouter($this->router);
				$module->onModuleBoot();
				$module->setEnabled(true);
			}
		}

		// Give an access from Twig
		$this->globalAccessorObject->set('modules', $modules);
	}
}
