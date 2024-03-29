<?php

namespace Etu\Core\UserBundle\Sync\Iterator;

use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToRemove;

/**
 * Iterator for synchronization process.
 */
class RemoveIterator implements \Iterator
{
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var ElementToRemove[]
     */
    private $elements;

    /**
     * @param ElementToRemove[] $elements
     */
    public function __construct(array $elements)
    {
        $this->position = 0;
        $this->elements = [];

        foreach ($elements as $element) {
            $this->elements[] = $element;
        }
    }

    /**
     * @param $key
     *
     * @return ElementToRemove
     */
    public function get($key)
    {
        return $this->elements[$key];
    }

    /**
     * @return Element\ElementToRemove[]
     */
    public function all()
    {
        return $this->elements;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * Return the current element.
     *
     * @see http://php.net/manual/en/iterator.current.php
     *
     * @return mixed Can return any type
     */
    public function current()
    {
        return $this->elements[$this->position];
    }

    /**
     * Move forward to next element.
     *
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element.
     *
     * @see http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid.
     *
     * @see http://php.net/manual/en/iterator.valid.php
     *
     * @return bool the return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure
     */
    public function valid()
    {
        return isset($this->elements[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
