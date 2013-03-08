<?php

namespace Etu\CoreBundle\Twig\Extension;

use Etu\CoreBundle\Menu\UserMenu\UserMenuRenderer;
use Etu\CoreBundle\Menu\UserMenu\UserMenuBuilder;


/**
 * Twig extension to call user menu renderer
 */
class UserMenuRendererExtension extends \Twig_Extension
{
	/**
	 * @var \Etu\CoreBundle\Menu\UserMenu\UserMenuRenderer
	 */
	protected $renderer;

	/**
	 * @var \Etu\CoreBundle\Menu\UserMenu\UserMenuBuilder
	 */
	protected $builder;

	/**
	 * @param \Etu\CoreBundle\Menu\UserMenu\UserMenuBuilder $builder
	 * @param \Etu\CoreBundle\Menu\UserMenu\UserMenuRenderer $renderer
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
		return array(
			'render_user_menu' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
		);
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

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'etu.user_menu';
	}
}
