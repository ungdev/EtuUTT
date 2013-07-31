<?php

namespace Etu\Core\UserBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\Badge;

class BadgesManager
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var EntityManager
	 */
	protected static $doctrine;

	/**
	 * @var Badge[]
	 */
	protected static $badges;

	/**
	 * @var Badge[]
	 */
	protected static $initialized = false;


	/**
	 * @param Registry $doctrine
	 */
	public function __construct(Registry $doctrine)
	{
		$this->em = $doctrine->getManager();
	}

	/**
	 * Freeze badges from the database
	 */
	public function onKernelRequest()
	{
		self::$doctrine = $this->em;
	}

	/**
	 * @param $serie
	 * @param $level
	 * @return Badge
	 * @throws \InvalidArgumentException
	 */
	public static function findBySerie($serie, $level = 1)
	{
		if (! self::$initialized) {
			self::initialize();
		}

		if (! isset(self::$badges[$serie.$level])) {
			throw new \InvalidArgumentException('Invalid badge reference');
		}

		return self::$badges[$serie.$level];
	}

	/**
	 * Initialize the badges list
	 */
	protected static function initialize()
	{
		/** @var Badge[] $badges */
		$badges = self::$doctrine->getRepository('EtuUserBundle:Badge')->findBy(array(), array(
			'serie' => 'ASC'
		));

		foreach ($badges as $badge) {
			self::$badges[$badge->getSerie().$badge->getLevel()] = $badge;
		}
	}
}
