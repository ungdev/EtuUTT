<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Organization Group Action.
 *
 * @ORM\Table(name="etu_organization_group_actions")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class OrganizationGroupAction
{
    public const ACTION_MAILIST_ADD_MEMBER = 10;
    public const ACTION_MAILIST_REMOVE_MEMBER = 11;
    public const ACTION_ETUSIA_ADD_MEMBER = 20;
    public const ACTION_ETUSIA_REMOVE_MEMBER = 21;

    public static $actions = [
        self::ACTION_MAILIST_ADD_MEMBER => 'mailist_add_member',
        self::ACTION_MAILIST_REMOVE_MEMBER => 'mailist_remove_member',
        self::ACTION_ETUSIA_ADD_MEMBER => 'etusia_add_member',
        self::ACTION_ETUSIA_REMOVE_MEMBER => 'etusia_add_member',
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
     * @var OrganizationGroup
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\OrganizationGroup", inversedBy="actions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $group;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    protected $action;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $data;

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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set action.
     *
     * @param int $action
     *
     * @return OrganizationGroupAction
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return int
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set data.
     *
     * @param array $data
     *
     * @return OrganizationGroupAction
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return OrganizationGroupAction
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
     * @param \DateTime|null $deletedAt
     *
     * @return OrganizationGroupAction
     */
    public function setDeletedAt($deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt.
     *
     * @return \DateTime|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set group.
     *
     * @param \Etu\Core\UserBundle\Entity\OrganizationGroup $group
     *
     * @return OrganizationGroupAction
     */
    public function setGroup(\Etu\Core\UserBundle\Entity\OrganizationGroup $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return \Etu\Core\UserBundle\Entity\OrganizationGroup
     */
    public function getGroup()
    {
        return $this->group;
    }
}
