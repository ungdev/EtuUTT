<?php

namespace Etu\Module\ApiBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/developers")
 */
class MainController extends Controller
{
	/**
	 * @Route("", name="developers_index")
	 * @Template()
	 */
	public function indexAction($name)
	{
		return array('name' => $name);
	}
}
