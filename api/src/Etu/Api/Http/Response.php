<?php

namespace Etu\Api\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * API formatted response
 */
class Response extends BaseResponse
{
	const STATUS_ERROR = 'error';
	const STATUS_WARNING = 'warning';
	const STATUS_SUCCESS = 'success';

	const BAD_REQUEST = 400;
	const UNAUTHORIZED = 401;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const NOT_ACCEPTABLE = 406;
	const GONE = 410;
	const INTERNAL_SERVER_ERROR = 500;
	const NOT_IMPLEMENTED = 501;
	const BAD_GATEWAY = 502;
	const SERVICE_UNAVAILABLE = 503;
	const GATEWAY_TIMEOUT = 504;

	const CONTENT_TYPE_JSON = 'application/json';
	const CONTENT_TYPE_XML = 'application/xml';

	/**
	 * @var Dumper\DumperInterface[]
	 */
	protected static $dumpers = array();

	/**
	 * @var string
	 */
	protected static $contentType = self::CONTENT_TYPE_JSON;

	/**
	 * @var array
	 */
	protected static $supportedContentTypes = array(
		self::CONTENT_TYPE_JSON,
		self::CONTENT_TYPE_XML,
	);

	/**
	 * @param string $status
	 * @param int $code
	 * @param array $data
	 */
	public function __construct($status, $code = 200, $data = array())
	{
		$data = array(
			'http' => array(
				'status' => $status,
				'code' => $code,
				'message' => (isset(self::$statusTexts[$code])) ? self::$statusTexts[$code] : self::$statusTexts[200]
			),
			'body' => $data
		);

		$content = null;

		foreach (self::$dumpers as $dumper) {
			if ($dumper->supports(self::$contentType)) {
				$content = $dumper->dump($data);
			}
		}

		if (! $content) {
			$jsonDumper = new Dumper\JsonDumper();
			$content = $jsonDumper->dump($data);
			self::$contentType = self::CONTENT_TYPE_JSON;
		}

		parent::__construct($content, $code);

		$this->headers->set('Content-Type', self::$contentType);
		$this->setCharset('utf-8');
	}

	/**
	 * @param Dumper\DumperInterface $dumper
	 */
	public static function registerDumper(Dumper\DumperInterface $dumper)
	{
		self::$dumpers[] = $dumper;
	}

	/**
	 * @param string $contentType
	 */
	public static function setContentType($contentType)
	{
		if (in_array($contentType, self::$supportedContentTypes)) {
			self::$contentType = $contentType;
		}
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	public static function useRequestAcceptedContentType(Request $request)
	{
		if (! $request->headers->has('Accept')) {
			self::$contentType = self::CONTENT_TYPE_JSON;
			return false;
		}

		$acceptHeader = $request->headers->get('Accept');

		if (in_array($acceptHeader, self::$supportedContentTypes)) {
			self::$contentType = $acceptHeader;
			return true;
		}

		$acceptTypes = explode(',', $acceptHeader);

		foreach ($acceptTypes as $acceptType) {
			$acceptType = explode(';', $acceptType);
			$acceptType = $acceptType[0];

			if (in_array($acceptType, self::$supportedContentTypes)) {
				self::$contentType = $acceptType;
				return true;
			}
		}

		self::$contentType = self::CONTENT_TYPE_JSON;
		return false;
	}

	/**
	 * @param $code
	 * @param null $message
	 * @return self
	 */
	public static function error($code, $message = null)
	{
		if ($message) {
			return new self(self::STATUS_ERROR, $code, array(
				'problem' => $message
			));
		} else {
			return new self(self::STATUS_ERROR, $code);
		}
	}

	/**
	 * @param array $data
	 * @return self
	 */
	public static function warning($data = array())
	{
		return new self(self::STATUS_WARNING, 200, $data);
	}

	/**
	 * @param array $data
	 * @return self
	 */
	public static function success($data = array())
	{
		return new self(self::STATUS_SUCCESS, 200, $data);
	}
}
