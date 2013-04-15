<?php

namespace Etu\Core\CoreBundle\Menu\OrgaMenu;

/**
 * User menu renderer: display the menu builder using user menu template.
 */
class OrgaMenuRenderer
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
	 * Render the menu in the user menu context
	 *
	 * @param \Etu\Core\CoreBundle\Menu\OrgaMenu\OrgaMenuBuilder $builder
	 * @return string
	 */
	public function render(OrgaMenuBuilder $builder)
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

		return $this->twig->render('EtuCoreBundle:Menu:user_menu.html.twig', array('items' => $sortedItems));
	}
}
