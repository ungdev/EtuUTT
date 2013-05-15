<?php

namespace Etu\Module\WikiBundle\Twig\Extension;

use Etu\Module\WikiBundle\Entity\Page;
use Etu\Module\WikiBundle\Model\PermissionsChecker;
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
			'wiki_can_view' => new \Twig_Function_Method($this, 'canView'),
			'wiki_can_edit' => new \Twig_Function_Method($this, 'canEdit'),
			'wiki_can_delete' => new \Twig_Function_Method($this, 'canDelete'),
			'wiki_can_create' => new \Twig_Function_Method($this, 'canCreate'),
		);
	}

	/**
	 * @param UserInterface $user
	 * @param Page          $page
	 * @return bool
	 */
	public function canView(UserInterface $user, Page $page)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canView($page);
	}

	/**
	 * @param UserInterface $user
	 * @param Page          $page
	 * @return bool
	 */
	public function canEdit(UserInterface $user, Page $page)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canEdit($page);
	}

	/**
	 * @param UserInterface $user
	 * @param Page          $page
	 * @return bool
	 */
	public function canDelete(UserInterface $user, Page $page)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canDelete($page);
	}

	/**
	 * @param UserInterface $user
	 * @param Page          $page
	 * @return bool
	 */
	public function canCreate(UserInterface $user, Page $page)
	{
		$checker = new PermissionsChecker($user);

		return $checker->canCreate($page);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'etu.wiki_permissions';
	}
}
