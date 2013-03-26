<?php

namespace Etu\Module\BugsBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class BugsController
 * @package Etu\Module\BugsBundle\Controller
 *
 * @Route("/bugs")
 */
class BugsController extends Controller
{
	/**
	 * @Route("/", name="bugs_index")
	 * @Template()
	 */
	public function indexAction()
	{
		return array(
			'bugs' => array()
		);
	}

	/**
	 * @Route("/closed", name="bugs_closed")
	 * @Template()
	 */
	public function closedAction()
	{
		return array(
			'bugs' => array()
		);
	}

	/**
	 * @Route("/{number}-{slug}", requirements = {"slug" = "\d+"}, name="bugs_view")
	 * @Template()
	 */
	public function viewAction($slug, $number)
	{
		return array();
	}
}
