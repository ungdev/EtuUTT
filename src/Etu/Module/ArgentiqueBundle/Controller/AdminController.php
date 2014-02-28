<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

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
    private static $authorized = ['admin', 'argentiq'];

	/**
	 * @Route("", name="argentique_admin_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUser()->getIsOrga() || ! in_array($this->getUser()->getLogin(), self::$authorized)) {
            throw new AccessDeniedHttpException('Only Argentique is authorized');
        }
	}
}
