<?php

namespace Etu\Core\CoreBundle\Menu\Sidebar;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Default sidebar. Edited by controllers on the fly.
 */
class SidebarBuilder
{
    /**
     * @var array
     */
    protected $blocks;

    /**
     * @var int
     */
    protected $lastPosition;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->blocks = [];
        $this->lastPosition = 0;

        $this
            ->addBlock('base.sidebar.services.title')
                ->add('base.user.menu.emails')
                    ->setIcon('mails.png')
                    ->setUrl('http://mail.utt.fr')
                    ->setLinkAttribute('target', '_blank')
                ->end()
            ->end()
            ->getBlock('base.sidebar.services.title')
                ->add('base.user.menu.buckutt')
                    ->setIcon('duck.png')
                    ->setPosition(3)
                    ->setUrl('https://buck.utt.fr')
                    ->setLinkAttribute('target', '_blank')
                ->end()
            ->end()
            ->addBlock('base.sidebar.etu.title')
                ->add('base.sidebar.etu.items.team')
                    ->setIcon('users.png')
                    ->setUrl($router->generate('contributors'))
                ->end()
            ->end();
    }

    /**
     * @param string $id
     *
     * @return \Etu\Core\CoreBundle\Menu\Sidebar\SidebarBlockBuilder
     */
    public function addBlock($id)
    {
        ++$this->lastPosition;

        $this->blocks[$id] = new SidebarBlockBuilder($this, $id);
        $this->blocks[$id]->setPosition($this->lastPosition);

        return $this->blocks[$id];
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function hasBlock($id)
    {
        return isset($this->blocks[$id]);
    }

    /**
     * @param string $id
     *
     * @return SidebarBlockBuilder
     */
    public function getBlock($id)
    {
        if (!$this->hasBlock($id)) {
            return null;
        }

        return $this->blocks[$id];
    }

    /**
     * @param string $id
     *
     * @return SidebarBuilder
     */
    public function removeBlock($id)
    {
        if (isset($this->blocks[$id])) {
            unset($this->blocks[$id]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
}
