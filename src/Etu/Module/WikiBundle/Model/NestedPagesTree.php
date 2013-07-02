<?php

namespace Etu\Module\WikiBundle\Model;

use Etu\Module\WikiBundle\Entity\Category;
use Etu\Module\WikiBundle\Entity\Page;

/**
 * Class NestedPagesTree
 * @package Etu\Module\WikiBundle\Model
 */
class NestedPagesTree
{
	/**
	 * @var Category[]
	 */
	protected $categories;

	/**
	 * @var Page[]
	 */
	protected $pages;

	/**
	 * @param Category[] $categories
	 * @param Page[] $pages
	 */
	public function __construct(array $pages, array $categories)
	{
		foreach ($pages as $page) {
			$this->pages[$page->getId()] = $page;
		}

		foreach ($categories as $category) {
			$this->categories[$category->getId()] = $category;
		}
	}

	/**
	 * @return \Etu\Module\WikiBundle\Entity\Page[]
	 */
	public function getNestedTree()
	{
		foreach ((array) $this->pages as $key => $page) {
			if ($page->getCategory()) {
				$this->categories[$page->getCategory()->getId()]->pages[] = $page;
				unset($this->pages[$key]);
			}
		}

		foreach ($this->categories as $key => $category) {
			$this->categories[$key]->children = $this->getChildren($category);

			if (! empty($category->children)) {
				$this->categories[$key]->hasChildren = true;
			}
		}

		foreach ($this->categories as $key => $category) {
			if ($category->getDepth() != 0) {
				unset($this->categories[$key]);
			}
		}

		return array('pages' => $this->pages, 'categories' => $this->categories);
	}

	/**
	 * @param Category $category
	 * @return Category[]
	 */
	public function getChildren(Category $category)
	{
		/** @var $children Category[] */
		$children = array();

		foreach ($this->categories as $c) {
			if ($c->getParent() && $c->getParent()->getId() == $category->getId()) {
				$c->children = $this->getChildren($c);
				$children[] = $c;
			}
		}

		return $children;
	}

	/**
	 * @param Page $page
	 * @return Category[]
	 */
	public function getBreadcrumbFor(Page $page)
	{
		$category = $page->getCategory();
		$breadcrumb = array($category);

		while ($category->getParent() instanceof Category) {
			$category = $category->getParent();
			$breadcrumb[] = $category;
		}

		return array_reverse($breadcrumb);
	}
}
