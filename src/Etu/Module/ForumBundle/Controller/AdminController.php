<?php

namespace Etu\Module\ForumBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/forum")
 */
class AdminController extends Controller
{
	/**
	 * @Route("", name="admin_forum_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->isAdmin()) {
			return $this->createAccessDeniedResponse();
		}
	}
}

