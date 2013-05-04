<?php

namespace Etu\Core\CoreBundle\Framework\Definition;

/**
 * Class Permission
 * @package Etu\Core\CoreBundle\Framework\Definition
 *
 * Permission class: definition for a module permission.
 */
class Permission
{
	const DEFAULT_ENABLED = true;
	const DEFAULT_DISABLED = false;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var boolean
	 */
	protected $defaultEnabled;


	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param boolean $defaultEnabled
	 * @param string $description
	 */
	public function __construct($name, $defaultEnabled, $description)
	{
		$this->name = $name;
		$this->defaultEnabled = $defaultEnabled;
		$this->description = $description;
	}

	/**
	 * @param boolean $defaultEnabled
	 * @return Permission
	 */
	public function setDefaultEnabled($defaultEnabled)
	{
		$this->defaultEnabled = $defaultEnabled;

		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getDefaultEnabled()
	{
		return $this->defaultEnabled;
	}

	/**
	 * @param string $description
	 * @return Permission
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $name
	 * @return Permission
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
}
