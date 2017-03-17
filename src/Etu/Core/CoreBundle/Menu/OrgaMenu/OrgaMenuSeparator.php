<?php

namespace Etu\Core\CoreBundle\Menu\OrgaMenu;

/**
 * User menu separator.
 */
class OrgaMenuSeparator
{
    /**
     * @var int
     */
    protected $position;

    /**
     * @var OrgaMenuBuilder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $role;

    /**
     * @param OrgaMenuBuilder $builder
     */
    public function __construct(OrgaMenuBuilder $builder)
    {
        $this->builder = $builder;
        $this->position = 0;
    }

    /**
     * @return OrgaMenuBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @param int $position
     *
     * @return OrgaMenuItem
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets the role to use.
     *
     * @param string $role
     *
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Retrieves the currently set role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return bool
     */
    public function isSeparator()
    {
        return true;
    }

    /**
     * @return OrgaMenuBuilder
     */
    public function end()
    {
        return $this->builder;
    }
}
