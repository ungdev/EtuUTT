<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

use Etu\Core\CoreBundle\Menu\OrgaMenu\OrgaMenuBuilder;
use Etu\Core\CoreBundle\Menu\OrgaMenu\OrgaMenuRenderer;

/**
 * Twig extension to call organization menu renderer.
 */
class OrgaMenuRendererExtension extends \Twig_Extension
{
    /**
     * @var \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuRenderer
     */
    protected $renderer;

    /**
     * @var \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder
     */
    protected $builder;

    public function __construct(OrgaMenuBuilder $builder, OrgaMenuRenderer $renderer)
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
            new \Twig_SimpleFunction('render_orga_menu', [$this, 'render'], ['is_safe' => ['html']]),
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
