<?php

namespace Etu\Core\UserBundle\Sync;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToImport;
use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToRemove;
use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToUpdate;
use Etu\Core\UserBundle\Sync\Iterator\ImportIterator;
use Etu\Core\UserBundle\Sync\Iterator\RemoveIterator;
use Etu\Core\UserBundle\Sync\Iterator\UpdateIterator;

/**
 * Synchronization process.
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
     * @var array
     */
    protected $toUpdate;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     * @param array    $toAddInDb
     * @param array    $toRemoveFromDb
     * @param array    $toUpdate
     */
    public function __construct(Registry $doctrine, array $toAddInDb, array $toRemoveFromDb, array $toUpdate)
    {
        $this->toAdd = [];
        $this->toRemove = [];
        $this->toUpdate = [];
        $this->doctrine = $doctrine;

        foreach ($toAddInDb as $login => $object) {
            $this->toAdd[$login] = new ElementToImport($this->doctrine, $object);
        }

        foreach ($toRemoveFromDb as $login => $object) {
            $this->toRemove[$login] = new ElementToRemove($this->doctrine, $object);
        }

        foreach ($toUpdate as $login => $object) {
            $this->toUpdate[$login] = new ElementToUpdate($this->doctrine, $object);
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

    /**
     * @return UpdateIterator
     */
    public function getUpdateIterator()
    {
        return new UpdateIterator($this->toUpdate);
    }
}
