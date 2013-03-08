<?php

namespace Etu\CoreBundle\Twig\Extension;

use Etu\CoreBundle\Menu\Sidebar\SidebarBuilder;
use Etu\CoreBundle\Menu\Sidebar\SidebarRenderer;


/**
 * Twig extension to call sidebar renderer
 */
class SidebarRendererExtension extends \Twig_Extension
{
	/**
	 * @var \Etu\CoreBundle\Menu\Sidebar\SidebarRenderer
	 */
	protected $renderer;

	/**
	 * @var \Etu\CoreBundle\Menu\Sidebar\SidebarBuilder
	 */
	protected $builder;

	/**
	 * @param \Etu\CoreBundle\Menu\Sidebar\SidebarBuilder $builder
	 * @param \Etu\CoreBundle\Menu\Sidebar\SidebarRenderer $renderer
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
			'render_sidebar' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
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
	 * @return string
	 */
	public function getName()
	{
		return 'etu.sidebar';
	}
}
