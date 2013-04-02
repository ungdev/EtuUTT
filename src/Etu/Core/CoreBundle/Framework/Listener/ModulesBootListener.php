<?php

namespace Etu\Core\CoreBundle\Framework\Listener;

use Etu\Core\CoreBundle\Framework\Twig\GlobalAccessorObject;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ModulesBootListener
{
	/**
	 * @var SecurityContextInterface
	 */
	protected $securityContext;

	/**
	 * @var array
	 */
	protected $modules;

	/**
	 * @var Router
	 */
	protected $router;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @param SecurityContextInterface $securityContext
	 * @param \AppKernel               $kernel
	 */
	public function __construct(SecurityContextInterface $securityContext, \AppKernel $kernel)
	{
		$this->securityContext = $securityContext;
		$this->modules = $kernel->getModulesDefinitions();
		$this->router = $kernel->getContainer()->get('router');
		$this->container = $kernel->getContainer();
	}

	/**
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		// Boot modules
		foreach ($this->modules as &$module) {
			if ($module->mustBoot()) {
				$module->setContainer($this->container);
				$module->setRouter($this->router);
				$module->onModuleBoot();
				$module->setEnabled(true);
			}
		}

		// Create Twig accessor object
		$app = new GlobalAccessorObject($this->container->get('etu.core.modules_manager')->getModules());

		$this->container->get('twig')->addGlobal('etu', $app);
	}
}
