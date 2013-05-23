<?php

namespace Etu\Module\LoggerBundle\Logger;

use Etu\Module\LoggerBundle\Logger\Model\ExceptionParsed;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Response;

class RequestListener
{
	/**
	 * @param FilterControllerEvent $event
	 * @return bool
	 */
	public function onKernelController(FilterControllerEvent $event)
	{
		if (! $event->getRequest()->isXmlHttpRequest()) {
			$this->log($event->getRequest());
		}
	}

	/**
	 * @param Request $request
	 */
	protected function log(Request $request)
	{
		/** @var $template \Symfony\Bundle\FrameworkBundle\Templating\TemplateReference */
		$template = $request->get('_template');

		if (! $template) {
			return;
		}

		if (! file_exists(__DIR__.'/../Resources/objects/requests')) {
			file_put_contents(__DIR__.'/../Resources/objects/requests', serialize(array()));
		}

		$logs = unserialize(file_get_contents(__DIR__.'/../Resources/objects/requests'));

		$logs[] = array(
			'ip' => $request->getClientIp(),
			'url' => $request->getRequestUri(),
			'route' => $request->get('_route'),
			'controller' => $request->get('_controller'),
			'template' => $template->getLogicalName(),
			'locale' => $request->getLocale(),
			'method' => $request->getMethod(),
			'date' => new \DateTime(),
		);

		if (count($logs) > 500) {
			array_shift($logs);
		}

		file_put_contents(__DIR__.'/../Resources/objects/requests', serialize($logs));
	}
}
