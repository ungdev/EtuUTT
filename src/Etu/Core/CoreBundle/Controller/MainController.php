<?php

namespace Etu\Core\CoreBundle\Controller;


use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Etu\Core\CoreBundle\Framework\Module\ModulesManager;
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

		$em = $this->getDoctrine()->getManager();

		$notif = new Notification();
		$notif->setUser($this->getUser());
		$notif->setModule('user');
		$notif->setHelper('user_followed');
		$notif->setIsNew(true);
		$notif->addEntity($em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => 'ladunean')));

		return array('notif' => $notif);
	}
}
