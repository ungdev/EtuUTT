<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Organization.
 *
 * @ORM\Table(name="etu_organizations_members")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Member
{
    public const ROLE_PRESIDENT = 40;
    public const ROLE_V_PRESIDENT = 39;

    public const ROLE_TREASURER = 30;
    public const ROLE_V_TREASURER = 29;

    public const ROLE_SECRETARY = 20;
    public const ROLE_V_SECRETARY = 19;

    public const ROLE_MEMBER = 10;

    public static $roles = [
        self::ROLE_PRESIDENT => 'president',
        self::ROLE_V_PRESIDENT => 'vice_president',

        self::ROLE_TREASURER => 'treasurer',
        self::ROLE_V_TREASURER => 'vice_treasurer',

        self::ROLE_SECRETARY => 'secretary',
        self::ROLE_V_SECRETARY => 'vice_secretary',

        self::ROLE_MEMBER => 'member',
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User", inversedBy="memberships")
     * @ORM\JoinColumn()
     */
    protected $user;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization", inversedBy="memberships")
     * @ORM\JoinColumn()
     */
    protected $organization;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    protected $role;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $permissions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->role = self::ROLE_MEMBER;
        $this->permissions = [];
    }

    /**
     * @return int
     */
    public function isFromBureau()
    {
        return in_array($this->role, [
            self::ROLE_PRESIDENT, self::ROLE_SECRETARY, self::ROLE_TREASURER,
            self::ROLE_V_PRESIDENT, self::ROLE_V_SECRETARY, self::ROLE_V_TREASURER,
        ]);
    }

    /**
     * @return array
     */
    public static function getAvailableRoles()
    {
        return [
            self::ROLE_MEMBER => self::ROLE_MEMBER,
            self::ROLE_PRESIDENT => self::ROLE_PRESIDENT,
            self::ROLE_SECRETARY => self::ROLE_SECRETARY,
            self::ROLE_TREASURER => self::ROLE_TREASURER,
            self::ROLE_V_PRESIDENT => self::ROLE_V_PRESIDENT,
            self::ROLE_V_SECRETARY => self::ROLE_V_SECRETARY,
            self::ROLE_V_TREASURER => self::ROLE_V_TREASURER,
        ];
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
     *
     * @return Member
     */
    public function setDate(\DateTime $date)
    {
        $this->createdAt = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->createdAt;
    }

    /**
     * @param \Etu\Core\UserBundle\Entity\Organization $organization
     *
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
     *
     * @return Member
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @param string $permissionName
     *
     * @return bool
     */
    public function hasPermission($permissionName)
    {
        return in_array($permissionName, $this->permissions);
    }

    /**
     * @param string $permission
     *
     * @return Member
     */
    public function addPermission($permission)
    {
        if (!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
        }

        return $this;
    }

    /**
     * @param string $permission
     *
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
     *
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
     *
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

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set deletedAt.
     *
     * @param \DateTime $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt.
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}
