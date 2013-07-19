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
			'forum_can_read' => new \Twig_Function_Method($this, 'canRead')
		);
	}

	/**
	 * @param UserInterface $user
	 * @param Category      $category
	 * @return bool
	 */
	public function canRead(UserInterface $user, Category $category)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canRead($category);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'etu.forum_permissions';
	}
}
