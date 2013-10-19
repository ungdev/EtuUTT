<?php

namespace Etu\Core\CoreBundle\Framework\Api\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Tga\Api\Component\HttpFoundation\ResponseBuilder;
use Tga\Api\Framework\HttpKernel\Event\KernelExceptionEvent;
use Tga\Api\Component\HttpFoundation\Response;

use Etu\Core\CoreBundle\Framework\Api\Security\Exception\InvalidAppTokenException;
use Etu\Core\CoreBundle\Framework\Api\Security\Exception\InvalidUserTokenException;

class InvalidTokenListener
{
	public function onKernelException(KernelExceptionEvent $event)
	{
		/** @var ResponseBuilder $responseBuilder */
		$responseBuilder = $event->getKernel()->getContainer()->get('response_builder');

		if ($event->getException() instanceof InvalidAppTokenException) {
			$event->setResponse($responseBuilder->createErrorResponse(
				Response::HTTP_UNAUTHORIZED, 'Invalid application token'
			));
		} elseif ($event->getException() instanceof InvalidUserTokenException) {
			$event->setResponse($responseBuilder->createErrorResponse(
				Response::HTTP_UNAUTHORIZED, 'Invalid or expired user token'
			));
		}

		return null;
	}
}
