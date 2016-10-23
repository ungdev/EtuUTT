<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

use Etu\Core\CoreBundle\Menu\Sidebar\SidebarBuilder;
use Etu\Core\CoreBundle\Menu\Sidebar\SidebarRenderer;

/**
 * Twig extension to call sidebar renderer.
 */
class SidebarRendererExtension extends \Twig_Extension
{
    /**
     * @var \Etu\Core\CoreBundle\Menu\Sidebar\SidebarRenderer
     */
    protected $renderer;

    /**
     * @var \Etu\Core\CoreBundle\Menu\Sidebar\SidebarBuilder
     */
    protected $builder;

    /**
     * @param \Etu\Core\CoreBundle\Menu\Sidebar\SidebarBuilder  $builder
     * @param \Etu\Core\CoreBundle\Menu\Sidebar\SidebarRenderer $renderer
     */
    public function __construct(SidebarBuilder $builder, SidebarRenderer $renderer)
    {
        $this->builder = $builder;
        $this->renderer = $renderer;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('render_sidebar', array($this, 'render'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('render_sidebar_mobile', array($this, 'renderMobile'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Render the sidebar.
     *
     * @return string
     */
    public function render()
    {
        return $this->renderer->render($this->builder);
    }

    /**
     * Render the sidebar.
     *
     * @return string
     */
    public function renderMobile()
    {
        return $this->renderer->renderMobile($this->builder);
    }
}
