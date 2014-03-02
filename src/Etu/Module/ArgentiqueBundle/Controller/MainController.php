<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/argentique")
 */
class MainController extends Controller
{
	/**
	 * @Route("", name="argentique_index")
	 * @Template()
	 */
	public function indexAction()
	{
		return [
            'is_admin' => in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'))
        ];
	}
}
