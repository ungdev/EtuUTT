<?php

namespace Etu\Api\Annotations;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Operation
{
	private $method;

	/**
	 * Constructor.
	 *
	 * @param array $data An array of key/value parameters.
	 *
	 * @throws \BadMethodCallException
	 */
	public function __construct(array $data)
	{
		foreach ($data as $key => $value) {
			$method = 'set'.str_replace('_', '', $key);
			if (!method_exists($this, $method)) {
				throw new \BadMethodCallException(sprintf("Unknown property '%s' on annotation '%s'.", $key, get_class($this)));
			}
			$this->$method($value);
		}
	}

	/**
	 * @param mixed $method
	 * @return $this
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMethod()
	{
		return $this->method;
	}
}
