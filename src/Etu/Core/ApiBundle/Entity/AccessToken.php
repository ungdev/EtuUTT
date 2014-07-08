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
class AccessToken
{
    /**
     * @ORM\Column(type="string", length=40)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Session $session
     *
     * @ORM\ManyToOne(targetEntity="Session", inversedBy="accessTokens", cascade={"persist", "remove"})
     * @ORM\JoinColumn()
     */
    private $session;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=255)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token_expires", type="integer")
     */
    private $expires;
}
