<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Etu\Core\CoreBundle\Framework\Module\PermissionsCollection;

/**
 * Organization
 *
 * @ORM\Table(name="etu_organizations_members")
 * @ORM\Entity
 */
class Member
{
	const ROLE_PRESIDENT = 'president';
	const ROLE_TREASURER = 'treasurer';
	const ROLE_SECRETARY = 'secretary';
	const ROLE_V_PRESIDENT = 'vice_president';
	const ROLE_V_TREASURER = 'vice_treasurer';
	const ROLE_V_SECRETARY = 'vice_secretary';
	const ROLE_MEMBER = 'member';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var User $president
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $user;

	/**
	 * @var Organization $organization
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization")
	 * @ORM\JoinColumn()
	 */
	protected $organization;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="role", type="string", length=100)
	 */
	protected $role;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="date", type="datetime")
	 */
	protected $date;

	/**
	 * @var array
	 *
	 * @ORM\Column(name="permissions", type="array")
	 */
	protected $permissions;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->date = new \DateTime();
		$this->role = self::ROLE_MEMBER;
		$this->permissions = array();
	}

	/**
	 * @return array
	 */
	public static function getAvailableRoles()
	{
		return array(
			self::ROLE_MEMBER => self::ROLE_MEMBER,
			self::ROLE_PRESIDENT => self::ROLE_PRESIDENT,
			self::ROLE_SECRETARY => self::ROLE_SECRETARY,
			self::ROLE_TREASURER => self::ROLE_TREASURER,
			self::ROLE_V_PRESIDENT => self::ROLE_V_PRESIDENT,
			self::ROLE_V_SECRETARY => self::ROLE_V_SECRETARY,
			self::ROLE_V_TREASURER => self::ROLE_V_TREASURER
		);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param \DateTime $date
	 * @return Member
	 */
	public function setDate(\DateTime $date)
	{
		$this->date = $date;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\Organization $organization
	 * @return Member
	 */
	public function setOrganization(Organization $organization)
	{
		$this->organization = $organization;

		return $this;
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\Organization
	 */
	public function getOrganization()
	{
		return $this->organization;
	}

	/**
	 * @param array $permissions
	 * @return Member
	 */
	public function setPermissions(array $permissions)
	{
		$this->permissions = $permissions;

		return $this;
	}

	/**
	 * @param string $permissionName
	 * @return bool
	 */
	public function hasPermission($permissionName)
	{
		return in_array($permissionName, $this->permissions);
	}

	/**
	 * @param string $permission
	 * @return Member
	 */
	public function addPermission($permission)
	{
		if (! in_array($permission, $this->permissions)) {
			$this->permissions[] = $permission;
		}

		return $this;
	}

	/**
	 * @param string $permission
	 * @return Member
	 */
	public function removePermission($permission)
	{
		if (($key = array_search($permission, $this->permissions)) !== false) {
			unset($this->permissions[$key]);
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}

	/**
	 * @param string $role
	 * @return Member
	 */
	public function setRole($role)
	{
		if (in_array($role, self::getAvailableRoles())) {
			$this->role = $role;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * @param \Etu\Core\UserBundle\Entity\User $user
	 * @return Member
	 */
	public function setUser($user)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\User
	 */
	public function getUser()
	{
		return $this->user;
	}
}