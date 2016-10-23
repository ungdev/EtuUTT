<?php

namespace Etu\Core\CoreBundle\Menu\UserMenu;

/**
 * User menu separator.
 */
class UserMenuSeparator
{
    /**
     * @var int
     */
    protected $position;

    /**
     * @var UserMenuBuilder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $role;

    /**
     * @param UserMenuBuilder $builder
     */
    public function __construct(UserMenuBuilder $builder)
    {
        $this->builder = $builder;
        $this->position = 0;
    }

    /**
     * @return UserMenuBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @param int $position
     *
     * @return UserMenuItem
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
     * @return UserMenuBuilder
     */
    public function end()
    {
        return $this->builder;
    }
}
