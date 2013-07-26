<?php

namespace Etu\Module\BuckUTTBundle\Layer\History;

class HistoryItem
{
	const TYPE_BUY = 'buy';
	const TYPE_RELOAD = 'reload';

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @var string
	 */
	protected $object;

	/**
	 * @var string
	 */
	protected $point;

	/**
	 * @var string
	 */
	protected $fundation;

	/**
	 * @var string
	 */
	protected $amount;

	/**
	 * @var string
	 */
	protected $user;

	/**
	 * @param $amount
	 * @return $this
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @param $date
	 * @return $this
	 */
	public function setDate($date)
	{
		$this->date = $date;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @param $fundation
	 * @return $this
	 */
	public function setFundation($fundation)
	{
		$this->fundation = $fundation;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFundation()
	{
		return $this->fundation;
	}

	/**
	 * @param $object
	 * @return $this
	 */
	public function setObject($object)
	{
		$this->object = $object;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getObject()
	{
		return $this->object;
	}

	/**
	 * @param $point
	 * @return $this
	 */
	public function setPoint($point)
	{
		$this->point = $point;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPoint()
	{
		return $this->point;
	}

	/**
	 * @param $type
	 * @return $this
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param $user
	 * @return $this
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}
}
