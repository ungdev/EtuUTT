<?php

namespace Etu\Install\Requirement;

class Requirement
{
    /**
     * @var boolean
     */
    protected $fulfilled;

    /**
     * @var string
     */
    protected $description;

    /**
     * @param boolean $fulfilled
     * @param string $description
     */
    public function __construct($fulfilled, $description)
    {
        $this->fulfilled = $fulfilled;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return boolean
     */
    public function isFulfilled()
    {
        return $this->fulfilled;
    }
}