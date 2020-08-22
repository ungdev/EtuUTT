<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Model\BadgesManager;
use Gedmo\Mapping\Annotation as Gedmo;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="etu_badges")
 * @ORM\Entity()
 *
 */
class Badge
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The serie is the link between badges of same thing at differents levels.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $serie;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $picture;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     * @Assert\Length(min = "5")
     */
    protected $description;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    protected $level;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * Temporary variable to store uploaded file during photo update.
     *
     * @var UploadedFile
     *
     * @Assert\Image(maxSize = "4M", minWidth = 80, minHeight = 80)
     */
    public $file;

    /**
     * @param $serie
     * @param $name
     * @param $desc
     * @param $picture
     * @param int $level
     */
    public function __construct($serie, $name, $desc, $picture, $level = 1)
    {
        $this->serie = $serie;
        $this->name = $name;
        $this->description = $desc;
        $this->picture = $picture;
        $this->level = $level;
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->picture;
    }

    /**
     * @param \DateTime $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt($deletedAt)
    {
        $series = BadgesManager::findBadgesList();
        foreach ($series as $name => $badges) {
            if ($name == $this->serie) {
                if (count($badges) != $this->level) {
                    foreach ($badges as $level => $badge) {
                        if ($level > $this->level) {
                            $badge->setLevel($badge->getLevel() - 1);
                        }
                    }
                }

                break;
            }
            $this->deletedAt = $deletedAt;
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $level
     *
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $serie
     *
     * @return $this
     */
    public function setSerie($serie)
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * @return string
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * @param string $picture
     *
     * @return $this
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $users
     *
     * @return $this
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Upload the picture.
     *
     * @return bool
     */
    public function upload()
    {
        if (null === $this->file) {
            return false;
        }

        /*
         * Upload and resize
         */
        $imagine = new Imagine();

        // Create a transparent image
        $image = $imagine->create(new Box(200, 200), new Color('000', 100));

        // Create the logo thumbnail in a 200x200 box
        $thumbnail = $imagine->open($this->file->getPathname())
            ->thumbnail(new Box(200, 200), Image::THUMBNAIL_INSET);

        // Paste point
        $pastePoint = new Point(
            (200 - $thumbnail->getSize()->getWidth()) / 2,
            (200 - $thumbnail->getSize()->getHeight()) / 2
        );

        // Paste the thumbnail in the transparent image
        $image->paste($thumbnail, $pastePoint);

        // Save the result
        $image->save(__DIR__.'/../../../../../web/uploads/badges/'.$this->getSerie().'_'.$this->getLevel().'.png');

        $this->picture = $this->getSerie().'_'.$this->getLevel();

        return $this->picture;
    }
}
