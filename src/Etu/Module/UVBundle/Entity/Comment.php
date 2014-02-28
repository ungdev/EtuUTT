<?php

namespace Etu\Module\UVBundle\Entity;

use Etu\Core\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity
 * @ORM\Table(name="etu_uvs_comments")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Comment
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $user;

	/**
	 * @var UV $uv
	 *
	 * @ORM\ManyToOne(targetEntity="UV")
	 * @ORM\JoinColumn()
	 */
	protected $uv;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text")
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "15")
	 */
	protected $body;

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
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(type="datetime")
	 */
	protected $updatedAt;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(type="datetime", nullable = true)
	 */
	protected $deletedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Comment
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Comment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Comment
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Comment
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set user
     *
     * @param \Etu\Core\UserBundle\Entity\User $user
     * @return Comment
     */
    public function setUser(\Etu\Core\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Etu\Core\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set uv
     *
     * @param \Etu\Module\UVBundle\Entity\UV $uv
     * @return Comment
     */
    public function setUv(\Etu\Module\UVBundle\Entity\UV $uv = null)
    {
        $this->uv = $uv;

        return $this;
    }

    /**
     * Get uv
     *
     * @return \Etu\Module\UVBundle\Entity\UV
     */
    public function getUv()
    {
        return $this->uv;
    }
}
