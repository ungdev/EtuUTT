<?php

namespace Etu\Core\CoreBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Etu\Core\CoreBundle\Stats\TgaAudienceDriver;
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

	/**
	 * @Route("/stats", name="admin_stats")
	 * @Template()
	 */
	public function statsAction()
	{
		// Stats
		$statsDriver = new TgaAudienceDriver($this->getDoctrine());

		return array_merge(
			$statsDriver->getGlobalStats(),
			$statsDriver->getVisitorsStats(),
			$statsDriver->getTrafficStats($this->container->getParameter('etu.domain'))
		);
	}
}
