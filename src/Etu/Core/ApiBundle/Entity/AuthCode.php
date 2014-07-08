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
class Client
{
    /**
     * @ORM\Column(type="string", length=40)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40)
     */
    private $secret;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="auto_approve", type="boolean")
     */
    private $autoApprove = false;
}
