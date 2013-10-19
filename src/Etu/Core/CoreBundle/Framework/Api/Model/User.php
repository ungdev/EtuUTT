<?php

namespace Etu\Core\CoreBundle\Framework\Api\Model;

class User
{
	/**
	 * @param $data
	 */
	public function __construct($data)
	{
		foreach ($data as $key => $value) {
			if (in_array($key, array(
				'studentId', 'level', 'sexPrivacy', 'nationalityPrivacy', 'adressPrivacy',
				'adressPrivacy', 'postalCodePrivacy', 'cityPrivacy', 'countryPrivacy', 'birthdayPrivacy',
				'personnalMailPrivacy'
			))) {
				$value = (int) $value;
			} elseif (in_array($key, array('birthdayDisplayOnlyAge', 'isStudent', 'isExternal'))) {
				$value = (bool) $value;
			} elseif ($key == 'semestersHistory') {
				$value = unserialize($value);
			}

			$this->$key = $value;
		}
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return $this|bool
	 * @throws \BadMethodCallException
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get') {
			return $this->get(substr($name, 3));
		} elseif (substr($name, 0, 2) == 'is') {
			$property = $this->toUnderscores(substr($name, 2));

			if (property_exists($this, $property)) {
				return (boolean) $this->$property;
			}
		} elseif (substr($name, 0, 3) == 'set') {
			return $this->set(substr($name, 3), $arguments[0]);
		}

		throw new \BadMethodCallException(sprintf(
			'Call to undefined method %s::%s()', __CLASS__, $name
		));
	}

	/**
	 * @param $field
	 * @param $value
	 * @return $this
	 */
	public function set($field, $value)
	{
		$property = $this->toUnderscores($field);
		$this->$property = $value;

		return $this;
	}

	/**
	 * @param $field
	 * @return bool
	 */
	public function get($field)
	{
		$property = $this->toUnderscores($field);

		if (property_exists($this, $property)) {
			return $this->$property;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return get_object_vars($this);
	}

	/**
	 * @param $str
	 * @return mixed
	 */
	private function toUnderscores($str)
	{
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		return preg_replace_callback('/([A-Z])/', $func, $str);
	}
}
