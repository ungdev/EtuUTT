<?php

namespace Etu\Core\CoreBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Symfony\Component\HttpFoundation\Response;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
	/**
	 * @Route("", name="admin_index")
	 * @Template()
	 */
	public function indexAction()
	{
		return array();
	}
}
