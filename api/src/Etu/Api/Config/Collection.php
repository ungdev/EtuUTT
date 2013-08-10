<?php

namespace Etu\Api\Config;

class Collection extends \ArrayObject
{
	/**
	 * @param $index
	 * @return mixed
	 */
	public function get($index)
	{
		if ($this->has($index)) {
			return $this->offsetGet($index);
		}

		$result = array();
		$indexLength = strlen($index) + 1;

		foreach ($this->getArrayCopy() as $key => $value) {
			if (substr($key, 0, $indexLength) == $index.'.') {
				$result[str_replace($index.'.', '', $key)] = $value;
			}
		}

		return $result;
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
	 * @param $newval
	 * @return $this
	 */
	public function add($index, $newval = null)
	{
		if($newval !== null) {
			if(! $this->has($index)) {
				$this->set($index, array());
			}

			$array = $this->get($index);
			$array[] = $newval;

			$this->offsetSet($index, $array);
		} else {
			$array = $this->getArrayCopy();
			$array[] = $newval;

			$this->exchangeArray($array);
		}

		return $this;
	}

	/**
	 * @param $index
	 * @return $this
	 */
	public function remove($index)
	{
		$this->offsetUnset($index);
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

	/**
	 * @param array|Collection $array
	 * @return $this
	 */
	public function merge($array)
	{
		if ($array instanceof self) {
			$array = $array->getArrayCopy();
		}

		$this->exchangeArray(array_merge($this->getArrayCopy(), $array));
		return $this;
	}

	/**
	 * Convert collection to 2D collection with dot notation keys
	 *
	 * @return $this
	 */
	public function compile()
	{
		$elements = $this->convert($this->getArrayCopy());
		$remplacements = array();

		foreach ($elements as $key => $value) {
			if (is_string($value)) {
				$remplacements['%'.$key.'%'] = $value;
			}
		}

		foreach ($elements as $key => $value) {
			if (is_string($value)) {
				$elements[$key] = str_replace(
					array_keys($remplacements),
					array_values($remplacements),
					$value
				);

				if (substr($value, 0, 1) == '%' && substr($value, -1) == '%') {
					$aliasName = substr($value, 1, -1);

					if (array_key_exists($aliasName, $elements)) {
						$elements[$key] = $elements[$aliasName];
					}
				}
			}
		}

		$this->exchangeArray($elements);

		return $this;
	}

	/**
	 * @param array $array
	 * @return array
	 */
	private function convert(array $array)
	{
		$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
		$result = array();

		foreach($iterator as $leafValue) {
			$keys = array();

			foreach (range(0, $iterator->getDepth()) as $depth) {
				if (is_numeric($iterator->getSubIterator($depth)->key())) {
					break;
				}

				$keys[] = $iterator->getSubIterator($depth)->key();
			}

			$result[implode('.', $keys)][] = $leafValue;
		}

		foreach ($result as $key => $array) {
			if (count($array) == 1) {
				$result[$key] = $array[0];
			}
		}

		return $result;
	}
}
