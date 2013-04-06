<?php

namespace Etu\Core\UserBundle\Sync;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToImport;
use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToRemove;
use Etu\Core\UserBundle\Sync\Iterator\ImportIterator;
use Etu\Core\UserBundle\Sync\Iterator\RemoveIterator;

/**
 * Synchronization process
 */
class Process
{
	/**
	 * @var array
	 */
	protected $toAdd;

	/**
	 * @var array
	 */
	protected $toRemove;

	/**
	 * @var Registry
	 */
	protected $doctrine;

	/**
	 * @param Registry $doctrine
	 * @param array    $toAddInDb
	 * @param array    $toRemoveFromDb
	 */
	public function __construct(Registry $doctrine, array $toAddInDb, array $toRemoveFromDb)
	{
		$this->toAdd = array();
		$this->toRemove = array();
		$this->doctrine = $doctrine;

		foreach ($toAddInDb as $login => $object) {
			$this->toAdd[$login] = new ElementToImport($this->doctrine, $object);
		}

		foreach ($toRemoveFromDb as $login => $object) {
			$this->toRemove[$login] = new ElementToRemove($this->doctrine, $object);
		}
	}

	/**
	 * @return ImportIterator
	 */
	public function getImportIterator()
	{
		return new ImportIterator($this->toAdd);
	}

	/**
	 * @return RemoveIterator
	 */
	public function getRemoveIterator()
	{
		return new RemoveIterator($this->toRemove);
	}
}
