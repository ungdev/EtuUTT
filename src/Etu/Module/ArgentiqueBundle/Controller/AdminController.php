<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/argentique/admin")
 */
class AdminController extends Controller
{
	/**
	 * @Route("", name="argentique_admin_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'))) {
            throw new AccessDeniedHttpException('Only Argentique is authorized');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        return [];
	}
}
