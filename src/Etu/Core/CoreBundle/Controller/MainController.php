<?php

namespace Etu\Core\CoreBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Symfony\Component\HttpFoundation\Response;

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
		if (! $this->getUser()) {
			return $this->indexAnonymousAction();
		}

		if ($this->getUser() && $this->getUser()->getIsOrga()) {
			return $this->indexOrgaAction();
		}

		return $this->indexUserAction();
	}


	/**
	 * @return Response
	 */
	protected function indexAnonymousAction()
	{
		return $this->render('EtuCoreBundle:Main:indexAnonymous.html.twig');
	}

	/**
	 * @return Response
	 */
	protected function indexOrgaAction()
	{
		return $this->render('EtuCoreBundle:Main:indexOrga.html.twig');
	}

	/**
	 * @return Response
	 */
	protected function indexUserAction()
	{
		// Add a block to the sidebar about the current flux
		$this->getSidebarBuilder()
			->addBlock('flux.sidebar.parameters.title')
				->setPosition(0)
				->add('flux.sidebar.parameters.items.suscribtions')
					->setIcon('etu-icon-star')
					->setUrl('')
				->end()
				->add('flux.sidebar.parameters.items.parameters')
					->setIcon('etu-icon-gear')
					->setUrl('')
				->end()
			->end();

		return $this->render('EtuCoreBundle:Main:index.html.twig');
	}
}
