<?php

namespace Etu\Core\UserBundle\Model;

class Semester
{
	/**
	 * @var string
	 */
	protected $code;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var integer
	 */
	protected $year;

	/**
	 * @var \DateTime
	 */
	protected $begin;

	/**
	 * @var \DateTime
	 */
	protected $end;

	/**
	 * @param string $type
	 * @param integer $year
	 */
	public function __construct($type, $year)
	{
		$this->code = $type.$year;
		$this->type = $type;
		$this->year = (int) $year;

		if ($type == SemesterManager::SPRING) {
			$this->begin = \DateTime::createFromFormat('z', SemesterManager::FIRST_DAY_SPRING);
			$this->begin->setTime(0, 0, 1);

			$this->end = \DateTime::createFromFormat('z', SemesterManager::FIRST_DAY_AUTUMN - 1);
			$this->end->setTime(23, 59, 59);
		} else {
			$this->begin = \DateTime::createFromFormat('z', SemesterManager::FIRST_DAY_AUTUMN);
			$this->begin->setTime(0, 0, 1);

			$this->end = \DateTime::createFromFormat('z', SemesterManager::FIRST_DAY_SPRING - 1);
			$this->end->add(new \DateInterval('P1Y'));
			$this->end->setTime(23, 59, 59);
		}
	}

	/**
	 * @return \DateTime
	 */
	public function getBegin()
	{
		return $this->begin;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @return \DateTime
	 */
	public function getEnd()
	{
		return $this->end;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getYear()
	{
		return $this->year;
	}
}
