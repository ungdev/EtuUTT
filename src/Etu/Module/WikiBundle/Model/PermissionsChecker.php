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
		$this->memberships = ($this->user instanceof User) ? $this->user->getMemberships() : array();
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
		if ($page->getLevelToView() == Page::LEVEL_ASSO_ADMIN) {
			return $this->findMembership($page->getOrga()) instanceof Member &&
				$this->findMembership($page->getOrga())->isFromBureau();
		}

		// Orga membership required
		if ($page->getLevelToView() == Page::LEVEL_ASSO_MEMBER) {
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
		// Organization ? Can only view public and owned pages
		if ($this->user instanceof Organization) {
			return (
				$page->getLevelToEdit() == Page::LEVEL_CONNECTED
					|| ($page->getOrga() instanceof Organization && $page->getOrga()->getId() == $this->user->getId())
			);
		}

		if ($this->user->getIsAdmin()) {
			return true;
		}

		// Admin permission required
		if ($page->getLevelToEdit() == Page::LEVEL_ADMIN) {
			return $this->user->getIsAdmin();
		}

		// Orga membership required
		if ($page->getLevelToEdit() == Page::LEVEL_ASSO_ADMIN) {
			return $this->findMembership($page->getOrga()) instanceof Member &&
				$this->findMembership($page->getOrga())->isFromBureau();
		}

		// Orga membership required
		if ($page->getLevelToEdit() == Page::LEVEL_ASSO_MEMBER) {
			return $this->findMembership($page->getOrga()) instanceof Member;
		}

		// Connected user required
		return $this->user instanceof UserInterface;
	}

	/**
	 * @param Page $page
	 * @return bool
	 */
	public function canEditPermissions(Page $page)
	{
		// Organization ? Can only view public and owned pages
		if ($this->user instanceof Organization) {
			return (
				$page->getLevelToEditPermissions() == Page::LEVEL_CONNECTED
					|| $page->getOrga()->getId() == $this->user->getId()
			);
		}

		if ($this->user->getIsAdmin()) {
			return true;
		}

		// Admin permission required
		if ($page->getLevelToEditPermissions() == Page::LEVEL_ADMIN) {
			return $this->user->getIsAdmin();
		}

		// Orga membership required
		if ($page->getLevelToEditPermissions() == Page::LEVEL_ASSO_ADMIN) {
			return $this->findMembership($page->getOrga()) instanceof Member &&
				$this->findMembership($page->getOrga())->isFromBureau();
		}

		// Orga membership required
		if ($page->getLevelToEditPermissions() == Page::LEVEL_ASSO_MEMBER) {
			return $this->findMembership($page->getOrga()) instanceof Member;
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
		if ($this->user->getIsAdmin()) {
			return true;
		}

		return $this->findMembership($page->getOrga()) instanceof Member;
	}

	/**
	 * @param Page $page
	 * @return bool
	 */
	public function canDelete(Page $page)
	{
		if ($this->user->getIsAdmin()) {
			return true;
		}

		return $this->findMembership($page->getOrga()) instanceof Member;
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
