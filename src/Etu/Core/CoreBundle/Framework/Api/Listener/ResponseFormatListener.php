<?php

namespace Etu\Core\CoreBundle\Framework\Api\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Tga\Api\Component\HttpFoundation\ResponseBuilder;
use Tga\Api\Framework\HttpKernel\Event\KernelRequestEvent;

class ResponseFormatListener
{
	public function onKernelRequest(KernelRequestEvent $event)
	{
		/** @var ContainerInterface $container */
		$container = $event->getKernel()->getContainer();

		/** @var ResponseBuilder $responseBuilder */
		$responseBuilder = $container->get('response_builder');

		$query = $event->getRequest()->query;
		$headers = $event->getRequest()->headers;

		if ($query->has('format')) {
			if ($query->get('format') == 'xml') {
				$responseBuilder->setDumper($container->get('response.dumper.xml'));
			}
		} else {
			if ($headers->has('Accept')) {
				if ($headers->get('Accept') == 'application/xml') {
					$responseBuilder->setDumper($container->get('response.dumper.xml'));
				} else {
					$acceptTypes = explode(',', $headers->get('Accept'));

					if ($acceptTypes[0] == 'application/xml') {
						$responseBuilder->setDumper($container->get('response.dumper.xml'));
					}
				}
			}
		}

		return null;
	}
}
