<?php

namespace Etu\Module\CovoitBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Etu\Core\CoreBundle\Entity\City;
use Etu\Core\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Covoit.
 *
 * @ORM\Table(name="etu_covoits")
 * @ORM\Entity
 *
 */
class Covoit
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn()
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank()
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     */
    private $capacity = 3;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isFull = false;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", scale=2, precision=10)
     *
     * @Assert\NotBlank()
     */
    private $price;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $blablacarUrl;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\CoreBundle\Entity\City")
     * @ORM\JoinColumn()
     * @ORM\OrderBy("name")
     *
     * @Assert\NotBlank()
     */
    private $startCity;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     */
    private $startAdress;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     *
     * @Assert\NotBlank()
     */
    private $startHour;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\CoreBundle\Entity\City")
     * @ORM\JoinColumn()
     * @ORM\OrderBy("name")
     *
     * @Assert\NotBlank()
     */
    private $endCity;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     */
    private $endAdress;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     *
     * @Assert\NotBlank()
     */
    private $endHour;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var CovoitSubscription[]
     *
     * @ORM\OneToMany(targetEntity="Etu\Module\CovoitBundle\Entity\CovoitSubscription", mappedBy="covoit", cascade={"persist", "remove"})
     * @ORM\JoinColumn()
     */
    private $subscriptions;

    /**
     * @var CovoitMessage[]
     *
     * @ORM\OneToMany(targetEntity="Etu\Module\CovoitBundle\Entity\CovoitMessage", mappedBy="covoit", cascade={"persist", "remove"})
     * @ORM\JoinColumn()
     */
    private $messages;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $parts = [];

        if ($this->getStartCity()) {
            $parts[] = 'startCity: '.$this->getStartCity()->getName();
        }

        if ($this->getEndCity()) {
            $parts[] = 'endCity: '.$this->getEndCity()->getName();
        }

        if ($this->getDate()) {
            $parts[] = 'date: '.$this->getDate()->format('d/m/Y');
        }

        if ($this->getPrice()) {
            $parts[] = 'price: '.$this->getPrice();
        }

        return implode(', ', $parts);
    }

    /**
     * @Assert\Callback
     */
    public function isBlaBlaCarUrlValid(ExecutionContextInterface $context)
    {
        if (!empty($this->blablacarUrl)) {
            if (false === mb_strpos($this->blablacarUrl, 'http')) {
                $this->blablacarUrl = 'https://'.$this->blablacarUrl;
            }

            if (!in_array(parse_url($this->blablacarUrl, PHP_URL_HOST), ['www.covoiturage.fr', 'covoiturage.fr', 'www.blablacar.fr', 'blablacar.fr'])) {
                $context->buildViolation('Cette URL n\'est pas une URL BlaBlaCar valide')
                    ->atPath('blablacarUrl')
                    ->addViolation();
            }
        }
    }

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
     * Set phoneNumber.
     *
     * @param string $phoneNumber
     *
     * @return Covoit
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber.
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set notes.
     *
     * @param string $notes
     *
     * @return Covoit
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes.
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set capacity.
     *
     * @param int $capacity
     *
     * @return Covoit
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity.
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Set isFull.
     *
     * @param bool $isFull
     *
     * @return Covoit
     */
    public function setIsFull($isFull)
    {
        $this->isFull = $isFull;

        return $this;
    }

    /**
     * Get isFull.
     *
     * @return bool
     */
    public function getIsFull()
    {
        return $this->isFull;
    }

    /**
     * Set price.
     *
     * @param string $price
     *
     * @return Covoit
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Covoit
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function hasCancelationExpired()
    {
        $expirationDate = clone $this->date;
        $expirationDate->modify('-2 days');

        return new \DateTime() > $expirationDate;
    }

    /**
     * Set blablacarUrl.
     *
     * @param string $blablacarUrl
     *
     * @return Covoit
     */
    public function setBlablacarUrl($blablacarUrl)
    {
        $this->blablacarUrl = $blablacarUrl;

        return $this;
    }

    /**
     * Get blablacarUrl.
     *
     * @return string
     */
    public function getBlablacarUrl()
    {
        return $this->blablacarUrl;
    }

    /**
     * Set startAdress.
     *
     * @param string $startAdress
     *
     * @return Covoit
     */
    public function setStartAdress($startAdress)
    {
        $this->startAdress = $startAdress;

        return $this;
    }

    /**
     * Get startAdress.
     *
     * @return string
     */
    public function getStartAdress()
    {
        return $this->startAdress;
    }

    /**
     * Set startHour.
     *
     * @param string $startHour
     *
     * @return Covoit
     */
    public function setStartHour($startHour)
    {
        $this->startHour = $startHour;

        return $this;
    }

    /**
     * Get startHour.
     *
     * @return string
     */
    public function getStartHour()
    {
        return $this->startHour;
    }

    /**
     * Set endAdress.
     *
     * @param string $endAdress
     *
     * @return Covoit
     */
    public function setEndAdress($endAdress)
    {
        $this->endAdress = $endAdress;

        return $this;
    }

    /**
     * Get endAdress.
     *
     * @return string
     */
    public function getEndAdress()
    {
        return $this->endAdress;
    }

    /**
     * Set endHour.
     *
     * @param string $endHour
     *
     * @return Covoit
     */
    public function setEndHour($endHour)
    {
        $this->endHour = $endHour;

        return $this;
    }

    /**
     * Get endHour.
     *
     * @return string
     */
    public function getEndHour()
    {
        return $this->endHour;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Covoit
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
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return Covoit
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt.
     *
     * @param \DateTime $deletedAt
     *
     * @return Covoit
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

    /**
     * Set author.
     *
     * @param User $author
     *
     * @return Covoit
     */
    public function setAuthor(User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set startCity.
     *
     * @param City $startCity
     *
     * @return Covoit
     */
    public function setStartCity(City $startCity = null)
    {
        $this->startCity = $startCity;

        return $this;
    }

    /**
     * Get startCity.
     *
     * @return City
     */
    public function getStartCity()
    {
        return $this->startCity;
    }

    /**
     * Set endCity.
     *
     * @param City $endCity
     *
     * @return Covoit
     */
    public function setEndCity(City $endCity = null)
    {
        $this->endCity = $endCity;

        return $this;
    }

    /**
     * Get endCity.
     *
     * @return City
     */
    public function getEndCity()
    {
        return $this->endCity;
    }

    /**
     * Add subscriptions.
     *
     * @return Covoit
     */
    public function addSubscription(CovoitSubscription $subscriptions)
    {
        $this->subscriptions[] = $subscriptions;

        return $this;
    }

    /**
     * Remove subscriptions.
     */
    public function removeSubscription(CovoitSubscription $subscriptions)
    {
        $this->subscriptions->removeElement($subscriptions);
    }

    /**
     * Get subscriptions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * @return bool
     */
    public function hasUser(User $user)
    {
        if ($this->author->getId() == $user->getId()) {
            return true;
        }

        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getUser()->getId() == $user->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add messages.
     *
     * @return Covoit
     */
    public function addMessage(CovoitMessage $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages.
     */
    public function removeMessage(CovoitMessage $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
