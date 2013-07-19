<?php

namespace Etu\Module\ForumBundle\Model;

use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\ForumBundle\Entity\Category;
use Etu\Module\ForumBundle\Entity\Permissions;
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
	 * @param Category $category
	 * @return bool
	 */
	public function canRead(Category $category)
	{

		if ($this->user->getIsAdmin()) {
			return true;
		}

		$permissions = new Permissions();

		foreach($category->getPermissions() as $value)
		{
			if($value->getBasic() == 1)
			{
				$permissions = $value;
			}
		}
		if($permissions->getRead()) return true;
		else {
			if ($this->user instanceof Organization) {
				foreach($category->getPermissions() as $value) {
					if($value->getOrganization() == $this->user) {
						$permissions = $value;
					}
				}
				if($permissions->getRead()) return true;
			}
			else {
				foreach($this->memberships as $value) {
					foreach($category->getPermissions() as $value) {
						if($value->getOrganization() == $this->user) {
							$permissions = $value;
						}
					}
					if($permissions->getRead()) return true;
				}
			}
		}
		
	}
}
