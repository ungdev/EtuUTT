<?php

namespace Etu\Core\CoreBundle\Menu\OrgaMenu;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Default user menu. Edited by controllers on the fly.
 */
class OrgaMenuBuilder
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
        $this->items = array();
        $this->lastPosition = 0;
        $this->separatorCount = 0;

        $this
            ->add('base.user.menu.admin')
                ->setIcon('gear.png')
                ->setUrl($router->generate('orga_admin'))
            ->end()
            ->add('base.user.menu.members')
                ->setIcon('users.png')
                ->setUrl($router->generate('orga_admin_members'))
            ->end()
            ->add('base.user.menu.logout')
                ->setIcon('control-power.png')
                ->setUrl($router->generate('user_logout'))
            ->end()
            // ->addSeparator()
            // ->add('base.user.menu.help')
            // 	->setIcon('question.png')
            // 	->setUrl('')
            // ->end()
        ;
    }

    /**
     * @param string $id
     *
     * @return \Etu\Core\CoreBundle\Menu\OrgaMenu\OrgaMenuItem
     */
    public function add($id)
    {
        ++$this->lastPosition;

        $this->items[$id] = new OrgaMenuItem($this, $id);
        $this->items[$id]->setPosition($this->lastPosition);

        return $this->items[$id];
    }

    /**
     * @return \Etu\Core\CoreBundle\Menu\OrgaMenu\OrgaMenuBuilder
     */
    public function addSeparator()
    {
        ++$this->lastPosition;
        ++$this->separatorCount;

        $item = new OrgaMenuSeparator($this);
        $item->setPosition($this->lastPosition);

        $this->items['separator-'.$this->separatorCount] = $item;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return OrgaMenuBuilder
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
