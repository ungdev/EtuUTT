<?php

namespace Etu\Module\ForumBundle\Twig\Extension;

use Etu\Module\ForumBundle\Entity\Category;
use Etu\Module\ForumBundle\Model\PermissionsChecker;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Twig extension to check user permissions on a page
 */
class PermissionsExtension extends \Twig_Extension
{
	/**
	 * @return array
	 */
	public function getFunctions()
	{
		return array(
			'forum_can_read' => new \Twig_Function_Method($this, 'canRead'),
			'forum_can_post' => new \Twig_Function_Method($this, 'canPost'),
			'forum_can_answer' => new \Twig_Function_Method($this, 'canAnswer'),
			'forum_can_edit' => new \Twig_Function_Method($this, 'canEdit'),
			'forum_can_sticky' => new \Twig_Function_Method($this, 'canSticky'),
			'forum_can_lock' => new \Twig_Function_Method($this, 'canLock'),
			'forum_can_move' => new \Twig_Function_Method($this, 'canMove')
		);
	}

	/**
	 * @param UserInterface $user
	 * @param Category      $category
	 * @return bool
	 */
	public function canRead($user, Category $category)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canRead($category);
	}

	/**
	 * @param UserInterface $user
	 * @param Category      $category
	 * @return bool
	 */
	public function canPost($user, Category $category)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canPost($category);
	}

	/**
	 * @param UserInterface $user
	 * @param Category      $category
	 * @return bool
	 */
	public function canAnswer($user, Category $category)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canAnswer($category);
	}

	/**
	 * @param UserInterface $user
	 * @param Category      $category
	 * @return bool
	 */
	public function canEdit($user, Category $category)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canEdit($category);
	}

	/**
	 * @param UserInterface $user
	 * @param Category      $category
	 * @return bool
	 */
	public function canSticky($user, Category $category)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canSticky($category);
	}


	/**
	 * @param UserInterface $user
	 * @param Category      $category
	 * @return bool
	 */
	public function canLock($user, Category $category)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canLock($category);
	}

	/**
	 * @param UserInterface $user
	 * @param Category      $category
	 * @return bool
	 */
	public function canMove($user, Category $category)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canMove($category);
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return 'etu.forum_permissions';
	}
}
