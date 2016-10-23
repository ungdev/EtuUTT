<?php

namespace Etu\Install\Requirement;

class Requirement
{
    /**
     * @var bool
     */
    protected $fulfilled;

    /**
     * @var string
     */
    protected $description;

    /**
     * @param bool   $fulfilled
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
     * @return bool
     */
    public function isFulfilled()
    {
        return $this->fulfilled;
    }
}
