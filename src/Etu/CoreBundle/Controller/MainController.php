<?php

namespace Etu\CoreBundle\Controller;

use Etu\CoreBundle\Framework\Controller;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/", name="homepage")
	 * @Template()
	 */
	public function indexAction()
	{
		// Add a block to the sidebar about the current flux
		$this->getSidebarBuilder()
			->addBlock('flux.sidebar.parameters.title')
				->setPosition(0)
				->add('flux.sidebar.parameters.items.suscribtions')
					->setIcon('etu-icon-star')
					->setUrl('')
				->end()
				->add('flux.sidebar.parameters.items.notifications')
					->setIcon('etu-icon-bell')
					->setUrl('')
				->end()
				->add('flux.sidebar.parameters.items.parameters')
					->setIcon('etu-icon-gear')
					->setUrl('')
				->end()
			->end();

		return array();
	}
}
