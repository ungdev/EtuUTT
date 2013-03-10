<?php

namespace Etu\Core\CoreBundle\Menu\Sidebar;

/**
 * Sidebar block builder.
 */
class SidebarBlockBuilder
{
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var integer
	 */
	protected $lastPosition;

	/**
	 * @var integer
	 */
	protected $position;

	/**
	 * @var SidebarBuilder
	 */
	protected $builder;

	/**
	 * @param SidebarBuilder $builder
	 * @param $title
	 */
	public function __construct(SidebarBuilder $builder, $title)
	{
		$this->items = array();
		$this->lastPosition = 0;
		$this->separatorCount = 0;
		$this->position = 0;
		$this->builder = $builder;

		$this->setTitle($title);
	}

	/**
	 * @param string $id
	 * @return SidebarItem
	 */
	public function add($id)
	{
		$this->lastPosition++;

		$this->items[$id] = new SidebarItem($this, $id);
		$this->items[$id]->setPosition($this->lastPosition);

		return $this->items[$id];
	}

	/**
	 * @param string $id
	 * @return SidebarBlockBuilder
	 */
	public function remove($id)
	{
		if (isset($this->items[$id])) {
			unset($this->items[$id]);
		}

		return $this;
	}

	/**
	 * @param string $title
	 * @return SidebarBlockBuilder
	 */
	public function setTitle($title)
	{
		$this->title = (string) $title;
		return $this;
	}

	/**
	 * @param int $position
	 * @return SidebarBlockBuilder
	 */
	public function setPosition($position)
	{
		$this->position = (integer) $position;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return SidebarBuilder
	 */
	public function end()
	{
		return $this->builder;
	}
}
