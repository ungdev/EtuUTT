<?php

namespace Etu\Api\Debug;

use Etu\Api\Http\AccessDeniedException;
use Etu\Api\Http\Response;

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ExceptionHandler
{
	/**
	 * If the devMode is enable, the handler will display a debugger.
	 * If not, the handler will display a simple message.
	 *
	 * @var boolean
	 */
	public $devMode;

	/**
	 * Constructor
	 */
	public function __construct($devMode = false)
	{
		$this->devMode = $devMode;
	}

	/**
	 * Register this object as exception handler
	 *
	 * @return bool
	 */
	public function register()
	{
		if (! set_exception_handler(array($this, 'handle'))) {
			return false;
		}

		return true;
	}

	/**
	 * Handle the exception
	 *
	 * @param \Exception $exception
	 * @return bool
	 */
	public function handle(\Exception $exception)
	{
		// Understand exception
		$exceptionParsed = new Parser\ExceptionParsed($exception);

		if ($exceptionParsed->getException() instanceof ResourceNotFoundException) {
			$statusCode = Response::NOT_FOUND;
		} elseif ($exceptionParsed->getException() instanceof AccessDeniedException) {
			$statusCode = Response::FORBIDDEN;
		} elseif ($exceptionParsed->getException() instanceof MethodNotAllowedException) {
			$statusCode = Response::METHOD_NOT_ALLOWED;
		} else {
			$statusCode = Response::INTERNAL_SERVER_ERROR;;
		}

		if ($this->devMode) {
			$response = new \Symfony\Component\HttpFoundation\Response();
			$response->headers->set('Content-Type', 'text/html');
			$response->setStatusCode($statusCode);

			ob_start();
			include __DIR__ . '/Resources/views/exception_dev.php';
			$content = ob_get_clean();

			$response->setContent($content);
		} else {
			$response = Response::error($statusCode);
		}

		$response->send();
		exit;
	}

	/**
	 * @param boolean $devMode
	 * @return ExceptionHandler
	 */
	public function setDevMode($devMode)
	{
		$this->devMode = $devMode;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getDevMode()
	{
		return $this->devMode;
	}

	/**
	 * @return boolean
	 */
	public function getObservers()
	{
		return $this->observers;
	}

	/**
	 * @param \Etu\Api\Debug\Templating\TemplateEngineInterface $templateEngine
	 * @return ExceptionHandler
	 */
	public function setTemplateEngine(Templating\TemplateEngineInterface $templateEngine)
	{
		$this->templateEngine = $templateEngine;

		return $this;
	}

	/**
	 * @return \Etu\Api\Debug\Templating\TemplateEngineInterface
	 */
	public function getTemplateEngine()
	{
		return $this->templateEngine;
	}
}
