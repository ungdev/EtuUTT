<?php

namespace Etu\Core\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_pages")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Page
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="slug", type="string", length=50)
	 */
	protected $slug;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="title", type="string", length=50)
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "2", max = "50")
	 */
	protected $title;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="content", type="text")
	 * @Assert\NotBlank()
	 */
	protected $content;

	/**
	 * @var \DateTime $created
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="createdAt", type="datetime")
	 */
	protected $createdAt;

	/**
	 * @var \DateTime $deletedAt
	 *
	 * @ORM\Column(name="deletedAt", type="datetime", nullable = true)
	 */
	protected $deletedAt;

	/**
	 * @param string $content
	 * @return Page
	 */
	public function setContent($content)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $title
	 * @return Page
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $slug
	 * @return Page
	 */
	public function setSlug($slug)
	{
		$this->slug = $slug;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSlug()
	{
		return $this->slug;
	}

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Page
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Page
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
}
