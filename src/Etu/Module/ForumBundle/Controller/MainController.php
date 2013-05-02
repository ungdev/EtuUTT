<?php

namespace Etu\Module\ForumBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/forum", name="forum_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if ($this->getUserLayer()->isUser()) {
			setcookie('user_id', $this->getUser()->getId(), time() + 15, '/');
			setcookie('user_hash', $this->getUser()->getSalt(), time() + 15, '/');
		} else {
			setcookie('user_id', 0, time() - 10, '/');
			setcookie('user_hash', 0, time() - 10, '/');
		}

		return array();
	}
}
