<?php

namespace Etu\Module\WikiBundle\Model;

use Etu\Module\WikiBundle\Entity\Page;

/**
 * Class NestedPagesTree
 * @package Etu\Module\WikiBundle\Model
 */
class NestedPagesTree
{
	/**
	 * @var Page[]
	 */
	protected $tree;

	/**
	 * @param Page[] $tree
	 */
	public function __construct(array $tree)
	{
		$this->tree = $tree;
	}

	/**
	 * @return \Etu\Module\WikiBundle\Entity\Page[]
	 */
	public function getNestedTree()
	{
		/** @var $tree Page[] */
		$tree = array();

		foreach ($this->tree as $node) {
			$node->children = $this->getChildren($node);
			$tree[] = $node;
		}

		foreach ($tree as $key => $node) {
			if ($node->getDepth() != 0) {
				unset($tree[$key]);
			}
		}

		return $tree;
	}

	/**
	 * @param Page $page
	 * @return \Etu\Module\WikiBundle\Entity\Page[]
	 */
	public function getChildren(Page $page)
	{
		/** @var $children Page[] */
		$children = array();

		foreach ($this->tree as $node) {
			if ($node->getLeft() > $page->getLeft() && $node->getRight() < $page->getRight()) {
				$node->children = $this->getChildren($node);
				$children[] = $node;
			}
		}

		foreach ($children as $key => $node) {
			if ($node->getDepth() != $page->getDepth() + 1) {
				unset($children[$key]);
			}
		}

		return $children;
	}
}
