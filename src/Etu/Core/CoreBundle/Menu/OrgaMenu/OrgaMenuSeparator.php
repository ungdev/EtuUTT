<?php

namespace Etu\Core\CoreBundle\Menu\OrgaMenu;

/**
 * User menu separator.
 */
class OrgaMenuSeparator
{
	/**
	 * @var integer
	 */
	protected $position;

	/**
	 * @var OrgaMenuBuilder
	 */
	protected $builder;

	/**
	 * @param OrgaMenuBuilder $builder
	 */
	public function __construct(OrgaMenuBuilder $builder)
	{
		$this->builder = $builder;
		$this->position = 0;
	}

	/**
	 * @return OrgaMenuBuilder
	 */
	public function getBuilder()
	{
		return $this->builder;
	}

	/**
	 * @param int $position
	 * @return OrgaMenuItem
	 */
	public function setPosition($position)
	{
		$this->position = (integer) $position;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * @return bool
	 */
	public function isSeparator()
	{
		return true;
	}

	/**
	 * @return OrgaMenuBuilder
	 */
	public function end()
	{
		return $this->builder;
	}
}
