<?php

namespace Etu\Module\LoggerBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\LoggerBundle\Logger\Model\ExceptionParsed;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/admin/logger/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="logger_index")
	 * @Template()
	 */
	public function indexAction($page = 1)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		$logs = unserialize(file_get_contents(__DIR__.'/../Resources/objects/errors'));

		foreach ($logs as $key => $log) {
			$logs[$key]['exception'] = ExceptionParsed::import($log['exception']);
		}

		$pagination = $this->get('knp_paginator')->paginate($logs, $page, 50);

		return array(
			'pagination' => $pagination
		);
	}
}
