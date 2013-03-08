<?php

namespace Etu\CoreBundle\Menu\UserMenu;

/**
 * User menu separator.
 */
class UserMenuSeparator
{
	/**
	 * @var integer
	 */
	protected $position;

	/**
	 * @var UserMenuBuilder
	 */
	protected $builder;

	/**
	 * @param UserMenuBuilder $builder
	 */
	public function __construct(UserMenuBuilder $builder)
	{
		$this->builder = $builder;
		$this->position = 0;
	}

	/**
	 * @return UserMenuBuilder
	 */
	public function getBuilder()
	{
		return $this->builder;
	}

	/**
	 * @param int $position
	 * @return UserMenuItem
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
	 * @return UserMenuBuilder
	 */
	public function end()
	{
		return $this->builder;
	}
}
