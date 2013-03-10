<?php

namespace Etu\Core\CoreBundle\Menu\Sidebar;

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
	 * @var integer
	 */
	protected $lastPosition;

	/**
	 * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
	 */
	public function __construct(\Symfony\Bundle\FrameworkBundle\Routing\Router $router)
	{
		$this->blocks = array();
		$this->lastPosition = 0;

		$this
			->addBlock('base.sidebar.services.title')
				->add('base.sidebar.services.items.uvs')
					->setIcon('etu-icon-briefcase')
					->setUrl('')
				->end()
				->add('base.sidebar.services.items.trombi')
					->setIcon('etu-icon-book')
					->setUrl('')
				->end()
				->add('base.sidebar.services.items.table')
					->setIcon('etu-icon-table')
					->setUrl('')
				->end()
				->add('base.sidebar.services.items.wiki')
					->setIcon('etu-icon-info')
					->setUrl('')
				->end()
			->end()
			->addBlock('base.sidebar.etu.title')
				->add('base.sidebar.etu.items.team')
					->setIcon('etu-icon-users')
					->setUrl('')
				->end()
				->add('base.sidebar.etu.items.suggest')
					->setIcon('etu-icon-comment')
					->setUrl('')
				->end()
				->add('base.sidebar.etu.items.bugs')
					->setIcon('etu-icon-warning')
					->setUrl('')
				->end()
			->end()
		;
	}

	/**
	 * @param string $id
	 * @return \Etu\Core\CoreBundle\Menu\Sidebar\SidebarBlockBuilder
	 */
	public function addBlock($id)
	{
		$this->lastPosition++;

		$this->blocks[$id] = new SidebarBlockBuilder($this, $id);
		$this->blocks[$id]->setPosition($this->lastPosition);

		return $this->blocks[$id];
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function hasBlock($id)
	{
		return isset($this->blocks[$id]);
	}

	/**
	 * @param string $id
	 * @return SidebarBlockBuilder
	 */
	public function getBlock($id)
	{
		if (! $this->hasBlock($id)) {
			return null;
		}

		return $this->blocks[$id];
	}

	/**
	 * @param string $id
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
