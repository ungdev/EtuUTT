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
    public const ANSWER_YES = 'yes';
    public const ANSWER_PROBABLY = 'probably';
    public const ANSWER_NO = 'no';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn()
     */
    protected $event;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\User")
     * @ORM\JoinColumn()
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20)
     */
    protected $answer;

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $answer
     */
    public function __construct(Event $event, User $user, $answer)
    {
        $this->event = $event;
        $this->user = $user;
        $this->answer = $answer;
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
     * Set answer.
     *
     * @param string $answer
     *
     * @return Answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer.
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set event.
     *
     * @param Event $event
     *
     * @return Answer
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return Answer
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
