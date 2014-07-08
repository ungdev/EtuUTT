<?php

namespace Etu\Core\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="oauth_clients",
 *      uniqueConstraints={@UniqueConstraint(name="secret", columns={"secret"})}
 * )
 */
class Session
{
    const OWNER_USER = 'user';
    const OWNER_CLIENT = 'client';

    /**
     * @ORM\Column(type="string", length=40)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Client $client
     *
     * @ORM\ManyToOne(targetEntity="Client", inversedBy="sessions", cascade={"persist", "remove"})
     * @ORM\JoinColumn()
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="owner_type", type="string", length=20)
     */
    private $ownerType = self::OWNER_USER;

    /**
     * @var string
     *
     * @ORM\Column(name="owner_id", type="string", length=255)
     */
    private $ownerId;
}
