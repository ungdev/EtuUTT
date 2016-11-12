<?php

namespace Etu\Core\CoreBundle\Menu\UserMenu;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Default user menu. Edited by controllers on the fly.
 */
class UserMenuBuilder
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @var int
     */
    protected $lastPosition;

    /**
     * @var int
     */
    protected $separatorCount;

    /**
     * Constructor
     * Initialize some default items.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->items = [];
        $this->lastPosition = 0;
        $this->separatorCount = 0;

        $this
            ->add('base.user.menu.flux')
                ->setIcon('edit-list.png')
                ->setUrl($router->generate('homepage'))
            ->end()
            ->add('base.user.menu.account')
                ->setIcon('user.png')
                ->setUrl($router->generate('user_profile'))
            ->end()
            ->add('base.user.menu.table')
                ->setIcon('table.png')
                ->setUrl($router->generate('user_schedule'))
                ->setRole('ROLE_CORE_SCHEDULE_OWN')
            ->end()
            ->add('base.user.menu.orgas')
                ->setIcon('bank.png')
                ->setUrl($router->generate('memberships_index'))
                ->setRole('ROLE_CORE_MEMBERSHIPS')
            ->end()
            ->add('base.user.menu.logout')
                ->setIcon('control-power.png')
                ->setUrl($router->generate('user_logout'))
            ->end()
            ->addSeparator('ROLE_CORE_ADMIN_HOME')
            ->add('base.user.menu.admin')
                ->setIcon('gear.png')
                ->setUrl($router->generate('admin_index'))
                ->setRole('ROLE_CORE_ADMIN_HOME')
            ->end()
        ;
    }

    /**
     * @param string $id
     *
     * @return \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuItem
     */
    public function add($id)
    {
        ++$this->lastPosition;

        $this->items[$id] = new UserMenuItem($this, $id);
        $this->items[$id]->setPosition($this->lastPosition);

        return $this->items[$id];
    }

    /**
     * @return \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder
     */
    public function addSeparator($role = '')
    {
        ++$this->lastPosition;
        ++$this->separatorCount;

        $item = new UserMenuSeparator($this);
        $item->setPosition($this->lastPosition);
        if (!empty($role)) {
            $item->setRole($role);
        }

        $this->items['separator-'.$this->separatorCount] = $item;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return UserMenuBuilder
     */
    public function remove($id)
    {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}
