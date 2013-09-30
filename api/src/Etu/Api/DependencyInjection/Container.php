<?php

namespace Etu\Api\DependencyInjection;

class Container extends \ArrayObject
{
	/**
	 * @param $index
	 * @return mixed
	 */
	public function get($index)
	{
		return $this->offsetGet($index);
	}

	/**
	 * @param $index
	 * @param $newval
	 * @return $this
	 */
	public function set($index, $newval)
	{
		$this->offsetSet($index, $newval);
		return $this;
	}

	/**
	 * @param $index
	 * @return bool
	 */
	public function has($index)
	{
		return $this->offsetExists($index);
	}
}
