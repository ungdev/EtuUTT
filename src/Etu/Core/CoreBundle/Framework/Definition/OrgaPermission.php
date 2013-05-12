<?php

namespace Etu\Core\CoreBundle\Framework\Definition;

/**
 * Definition for an organization permission
 */
class OrgaPermission extends Permission
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $description
	 */
	public function __construct($name, $description)
	{
		$this->name = $name;
		$this->defaultEnabled = false;
		$this->description = $description;
	}
}
