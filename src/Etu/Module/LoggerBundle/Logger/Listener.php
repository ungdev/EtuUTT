<?php

namespace Etu\Module\LoggerBundle\Logger;

use Etu\Module\LoggerBundle\Logger\Model\ExceptionParsed;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Listener
{
	/**
	 * @param GetResponseForExceptionEvent $event
	 * @return bool
	 */
	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		$exception = $event->getException();

		if ($exception instanceof HttpExceptionInterface) {
			if ($exception->getStatusCode() != 404) {
				$this->log($exception, $event->getRequest()->getClientIp());
			}
		} else {
			$this->log($exception, $event->getRequest()->getClientIp());
		}
	}

	/**
	 * @param \Exception $exception
	 * @param string $ip
	 */
	protected function log(\Exception $exception, $ip)
	{
		$exception = new ExceptionParsed($exception);

		if (file_exists(__DIR__.'/../Resources/objects/errors')) {
			file_put_contents(__DIR__.'/../Resources/objects/errors', serialize(array()));
		}

		$logs = unserialize(file_get_contents(__DIR__.'/../Resources/objects/errors'));

		$exceptionArray = array(
			'exception' => array(
				'message' => $exception->getMessage(),
				'class' => $exception->getClass(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'lineAround' => $exception->getLinesAround(),
				'trace' => $exception->getStringTrace(),
			),
			'children' => array(),
			'client' => $ip,
		);

		foreach ((array) $exception->getStack() as $child) {
			$exceptionArray['children'][] = array(
				'message' => $child->getMessage(),
				'class' => $child->getClass(),
				'file' => $child->getFile(),
				'line' => $child->getLine(),
				'lineAround' => $child->getLinesAround(),
				'trace' => $child->getStringTrace(),
			);
		}

		$logs[] = $exceptionArray;

		file_put_contents(__DIR__.'/../Resources/objects/errors', serialize($logs));
	}
}
