<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

use Etu\Core\CoreBundle\Menu\AdminMenu\AdminBuilder;
use Etu\Core\CoreBundle\Menu\Sidebar\SidebarRenderer;

/**
 * Twig extension to call admin menu renderer.
 */
class AdminMenuRendererExtension extends \Twig_Extension
{
    /**
     * @var \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuRenderer
     */
    protected $renderer;

    /**
     * @var \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder
     */
    protected $builder;

    public function __construct(AdminBuilder $builder, SidebarRenderer $renderer)
    {
        $this->builder = $builder;
        $this->renderer = $renderer;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_admin_menu', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Render the header menu.
     *
     * @return string
     */
    public function render()
    {
        return $this->renderer->render($this->builder);
    }
}
