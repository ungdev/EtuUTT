<?php

namespace Etu\CoreBundle\Menu\Sidebar;

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
	 * Constructor
	 *
	 * @param \Twig_Environment $twig
	 */
	public function __construct(\Twig_Environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * Render the sidebar
	 *
	 * @param SidebarBuilder $builder
	 * @return string
	 */
	public function render(SidebarBuilder $builder)
	{
		$blocks = $builder->getBlocks();

		$positions = array();
		$renderedBlocks = array();

		foreach ($blocks as $key => $block) {
			$positions[$key] = $block->getPosition();
		}

		asort($positions);

		foreach ($positions as $key => $position) {
			$block = $blocks[$key];
			$block->rendered = $this->renderBlock($blocks[$key]);

			$renderedBlocks[] = $block;
		}

		return $this->twig->render('EtuCoreBundle:Menu:sidebar.html.twig', array('blocks' => $renderedBlocks));
	}

	/**
	 * Render a block from the sidebar
	 *
	 * @param SidebarBlockBuilder $builder
	 * @return string
	 */
	public function renderBlock(SidebarBlockBuilder $builder)
	{
		$items = $builder->getItems();
		$positions = array();

		foreach ($items as $key => $item) {
			$positions[$key] = $item->getPosition();
		}

		asort($positions);

		$sortedItems = array();

		foreach ($positions as $key => $position) {
			$sortedItems[] = $items[$key];
		}

		return $this->twig->render('EtuCoreBundle:Menu:sidebar_block.html.twig', array('items' => $sortedItems));
	}
}
