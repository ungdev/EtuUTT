<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="sessions")
 * @ORM\Entity()
 */
class Session
{
    /**
     * @var binary
     *
     * @ORM\Column(type="binary", length=128)
     * @ORM\Id
     */
    protected $sess_id;

    /**
     * @var blob
     *
     * @ORM\Column(type="blob")
     */
    protected $sess_data;

    /**
     * @var unsigned int
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $sess_time;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     */
    protected $sess_lifetime;
}
