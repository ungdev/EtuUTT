<?php

namespace Etu\Core\CoreBundle\Menu\Sidebar;

/**
 * Sidebar renderer: display the builder using sidebar template.
 */
class SidebarRenderer
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Constructor.
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Render the sidebar.
     *
     * @return string
     */
    public function render(SidebarBuilder $builder)
    {
        $blocks = $builder->getBlocks();

        $positions = [];
        $renderedBlocks = [];

        foreach ($blocks as $key => $block) {
            $positions[$key] = $block->getPosition();
        }

        asort($positions);

        foreach ($positions as $key => $position) {
            $block = $blocks[$key];
            $block->rendered = $this->renderBlock($blocks[$key]);

            $renderedBlocks[] = $block;
        }

        return $this->twig->render('EtuCoreBundle:Menu:sidebar.html.twig', ['blocks' => $renderedBlocks]);
    }

    /**
     * Render the sidebar for mobile version.
     *
     * @return string
     */
    public function renderMobile(SidebarBuilder $builder)
    {
        $blocks = $builder->getBlocks();

        $positions = [];
        $renderedBlocks = [];

        foreach ($blocks as $key => $block) {
            $positions[$key] = $block->getPosition();
        }

        asort($positions);

        foreach ($positions as $key => $position) {
            $block = $blocks[$key];
            $block->rendered = $this->renderMobileBlock($blocks[$key]);

            $renderedBlocks[] = $block;
        }

        return $this->twig->render('EtuCoreBundle:Menu:sidebar_mobile.html.twig', ['blocks' => $renderedBlocks]);
    }

    /**
     * Render a block from the sidebar.
     *
     * @return string
     */
    public function renderBlock(SidebarBlockBuilder $builder)
    {
        $items = $builder->getItems();
        $positions = [];

        foreach ($items as $key => $item) {
            $positions[$key] = $item->getPosition();
        }

        asort($positions);

        $sortedItems = [];

        foreach ($positions as $key => $position) {
            $sortedItems[] = $items[$key];
        }

        return $this->twig->render('EtuCoreBundle:Menu:sidebar_block.html.twig', ['items' => $sortedItems]);
    }

    /**
     * Render a block from the sidebar (mobile version).
     *
     * @return string
     */
    public function renderMobileBlock(SidebarBlockBuilder $builder)
    {
        $items = $builder->getItems();
        $positions = [];

        foreach ($items as $key => $item) {
            $positions[$key] = $item->getPosition();
        }

        asort($positions);

        $sortedItems = [];

        foreach ($positions as $key => $position) {
            $sortedItems[] = $items[$key];
        }

        return $this->twig->render('EtuCoreBundle:Menu:sidebar_mobile_block.html.twig', ['items' => $sortedItems]);
    }
}
