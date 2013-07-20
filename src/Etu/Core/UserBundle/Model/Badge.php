<?php

namespace Etu\Core\UserBundle\Model;

class Badge implements \Serializable
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $pictureName;

	/**
	 * @var integer
	 */
	protected $level;

	/**
	 * @param string $name
	 * @param int $level
	 */
	public function __construct($name, $level = 1)
	{
		$this->name = $name;
		$this->pictureName = $name.'-'.$level;
		$this->level = $level;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->pictureName;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return serialize(array(
			'name' => $this->name,
			'pictureName' => $this->pictureName,
			'level' => $this->level,
		));
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 * @return void
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->name = $data['name'];
		$this->pictureName = $data['pictureName'];
		$this->level = $data['level'];
	}

	/**
	 * @param int $level
	 */
	public function setLevel($level)
	{
		$this->level = $level;
	}

	/**
	 * @return int
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $pictureName
	 */
	public function setPictureName($pictureName)
	{
		$this->pictureName = $pictureName;
	}

	/**
	 * @return string
	 */
	public function getPictureName()
	{
		return $this->pictureName;
	}
}
