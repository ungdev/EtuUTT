<?php

namespace Etu\Core\CoreBundle\Framework\Definition;

/**
 * Definition for a module permission.
 */
class Permission
{
    public const DEFAULT_ENABLED = true;
    public const DEFAULT_DISABLED = false;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $defaultEnabled;

    /**
     * Constructor.
     *
     * @param string $name
     * @param bool   $defaultEnabled
     * @param string $description
     */
    public function __construct($name, $defaultEnabled, $description)
    {
        $this->name = $name;
        $this->defaultEnabled = $defaultEnabled;
        $this->description = $description;
    }

    /**
     * @param bool $defaultEnabled
     *
     * @return Permission
     */
    public function setDefaultEnabled($defaultEnabled)
    {
        $this->defaultEnabled = $defaultEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDefaultEnabled()
    {
        return $this->defaultEnabled;
    }

    /**
     * @param string $description
     *
     * @return Permission
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $name
     *
     * @return Permission
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
