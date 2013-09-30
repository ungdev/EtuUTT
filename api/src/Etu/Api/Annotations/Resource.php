<?php

namespace Etu\Api\Annotations;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Resource
{
	private $path;
	private $name;
	private $requirements;
	private $defaults;

	/**
	 * Constructor.
	 *
	 * @param array $data An array of key/value parameters.
	 *
	 * @throws \BadMethodCallException
	 */
	public function __construct(array $data)
	{
		$this->requirements = array();
		$this->defaults = array();

		if (isset($data['value'])) {
			$data['path'] = $data['value'];
			unset($data['value']);
		}

		foreach ($data as $key => $value) {
			$method = 'set'.str_replace('_', '', $key);
			if (!method_exists($this, $method)) {
				throw new \BadMethodCallException(sprintf("Unknown property '%s' on annotation '%s'.", $key, get_class($this)));
			}
			$this->$method($value);
		}
	}

	/**
	 * @param array $defaults
	 * @return $this
	 */
	public function setDefaults($defaults)
	{
		$this->defaults = $defaults;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getDefaults()
	{
		return $this->defaults;
	}

	/**
	 * @param mixed $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $path
	 * @return $this
	 */
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @param array $requirements
	 * @return $this
	 */
	public function setRequirements($requirements)
	{
		$this->requirements = $requirements;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getRequirements()
	{
		return $this->requirements;
	}
}
