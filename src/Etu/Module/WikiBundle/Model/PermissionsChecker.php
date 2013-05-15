<?php

namespace Etu\Module\WikiBundle\Model;

use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\WikiBundle\Entity\Page;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Check permissions for a given user about a given page
 */
class PermissionsChecker
{
	/**
	 * @var User|Organization
	 */
	protected $user;

	/**
	 * @var Member[]
	 */
	protected $memberships;


	/**
	 * @param User|Organization $user
	 */
	public function __construct($user)
	{
		$this->user = $user;
		$this->memberships = $this->user->getMemberships();
	}

	/**
	 * @param Page $page
	 * @return bool
	 */
	public function canView(Page $page)
	{
		// Organization ? Can only view public and owned pages
		if ($this->user instanceof Organization) {
			return (
				$page->getLevelToView() == Page::LEVEL_CONNECTED
					|| $page->getOrga()->getId() == $this->user->getId()
			);
		}

		if ($this->user->getIsAdmin()) {
			return true;
		}

		// Admin permission required
		if ($page->getLevelToView() == Page::LEVEL_ADMIN) {
			return $this->user->getIsAdmin();
		}

		// Orga membership required
		if ($page->getLevelToEdit() == Page::LEVEL_ASSO) {
			return $this->findMembership($page->getOrga()) instanceof Member;
		}

		// Connected user required
		return $this->user instanceof UserInterface;
	}

	/**
	 * @param Page $page
	 * @return bool
	 */
	public function canEdit(Page $page)
	{
		// Organization ? Can not edit any page
		if ($this->user instanceof Organization) {
			return false;
		}

		if ($this->user->getIsAdmin()) {
			return true;
		}

		// Admin permission required
		if ($page->getLevelToEdit() == Page::LEVEL_ADMIN) {
			return $this->user->getIsAdmin();
		}

		// Asso membership required
		if ($page->getLevelToEdit() == Page::LEVEL_ASSO) {
			if ($membership = $this->findMembership($page->getOrga())) {
				return $membership->hasPermission('wiki.edit');
			} else {
				return false;
			}
		}

		// Connected user required
		return $this->user instanceof UserInterface;
	}

	/**
	 * @param Page $page
	 * @return bool
	 */
	public function canCreate(Page $page)
	{
		// Organization ? Can not edit any page
		if ($this->user instanceof Organization) {
			return false;
		}

		if ($this->user->getIsAdmin()) {
			return true;
		}

		// Admin permission required
		if ($page->getLevelToCreate() == Page::LEVEL_ADMIN) {
			return $this->user->getIsAdmin();
		}

		// Asso membership required
		if ($page->getLevelToCreate() == Page::LEVEL_ASSO) {
			if ($membership = $this->findMembership($page->getOrga())) {
				return $membership->hasPermission('wiki.create');
			} else {
				return false;
			}
		}

		// Connected user required
		return $this->user instanceof UserInterface;
	}

	/**
	 * @param Page $page
	 * @return bool
	 */
	public function canDelete(Page $page)
	{
		// Organization ? Can not edit any page
		if ($this->user instanceof Organization) {
			return false;
		}

		if ($this->user->getIsAdmin()) {
			return true;
		}

		// Admin permission required
		if ($page->getLevelToDelete() == Page::LEVEL_ADMIN) {
			return $this->user->getIsAdmin();
		}

		// Asso membership required
		if ($page->getLevelToDelete() == Page::LEVEL_ASSO) {
			if ($membership = $this->findMembership($page->getOrga())) {
				return $membership->hasPermission('wiki.delete');
			} else {
				return false;
			}
		}

		// Connected user required
		return $this->user instanceof UserInterface;
	}

	/**
	 * @param Organization $orga
	 * @return bool|Member
	 */
	protected function findMembership(Organization $orga)
	{
		foreach ($this->memberships as $membership) {
			if ($membership->getOrganization()->getId() == $orga->getId()) {
				return $membership;
			}
		}

		return false;
	}
}
