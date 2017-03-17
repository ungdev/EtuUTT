<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

use Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder;
use Etu\Core\CoreBundle\Menu\UserMenu\UserMenuRenderer;

/**
 * Twig extension to call user menu renderer.
 */
class UserMenuRendererExtension extends \Twig_Extension
{
    /**
     * @var \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuRenderer
     */
    protected $renderer;

    /**
     * @var \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder
     */
    protected $builder;

    /**
     * @param \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder  $builder
     * @param \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuRenderer $renderer
     */
    public function __construct(UserMenuBuilder $builder, UserMenuRenderer $renderer)
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
            new \Twig_SimpleFunction('render_user_menu', [$this, 'render'], ['is_safe' => ['html']]),
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
