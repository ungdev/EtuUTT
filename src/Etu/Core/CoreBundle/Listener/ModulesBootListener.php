<?php

namespace Etu\Core\CoreBundle\Listener;

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
		foreach ($this->modules as $module) {
			if ($module->mustBoot()) {
				$module->setContainer($this->container);
				$module->setRouter($this->router);
				$module->onModuleBoot();
				$module->setEnabled(true);
			}
		}
	}
}
