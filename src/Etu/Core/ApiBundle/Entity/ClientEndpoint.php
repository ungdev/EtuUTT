<?php

namespace Etu\Core\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="oauth_client_endpoints",
 *      uniqueConstraints={@UniqueConstraint(name="secret", columns={"secret"})}
 * )
 */
class ClientEndpoint
{
    /**
     * @ORM\Column(type="string", length=40)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Client $client
     *
     * @ORM\ManyToOne(targetEntity="Client", inversedBy="endpoints", cascade={"persist", "remove"})
     * @ORM\JoinColumn()
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="redirect_uri", type="string", length=40)
     */
    private $redirectUri;
}
