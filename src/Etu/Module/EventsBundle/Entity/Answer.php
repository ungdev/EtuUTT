<?php

namespace Etu\Module\EventsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Etu\Core\UserBundle\Entity\User;

/**
 * @ORM\Table(name="etu_events_answers")
 * @ORM\Entity(repositoryClass="EventRepository")
 */
class Answer
{
	const ANSWER_YES = 'yes';
	const ANSWER_PROBABLY = 'probably';
	const ANSWER_NO = 'no';

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var Event $event
	 *
	 * @ORM\ManyToOne(targetEntity="Event")
	 * @ORM\JoinColumn()
	 */
	protected $event;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
	 * @ORM\JoinColumn()
	 */
	protected $user;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="answer", type="string", length=20)
	 */
	protected $answer;

	/**
	 * @param Event $event
	 * @param User  $user
	 */
	public function __construct(Event $event, User $user)
	{
		$this->event = $event;
		$this->user = $user;
	}

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
     * Set answer
     *
     * @param string $answer
     * @return Answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set event
     *
     * @param \Etu\Module\EventsBundle\Entity\Event $event
     * @return Answer
     */
    public function setEvent(\Etu\Module\EventsBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \Etu\Module\EventsBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set user
     *
     * @param \Etu\Module\EventsBundle\Entity\User $user
     * @return Answer
     */
    public function setUser(\Etu\Module\EventsBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Etu\Module\EventsBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}