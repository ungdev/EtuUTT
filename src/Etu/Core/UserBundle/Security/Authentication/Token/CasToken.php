<?php

namespace Etu\Core\UserBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Cas token.
 */
class CasToken extends AbstractToken
{
    /**
     * Constructor.
     *
     * @param string|object            $user  The cas login or a UserInterface instance or an object implementing a __toString method
     * @param RoleInterface[]|string[] $roles An array of roles
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($user, array $roles = [])
    {
        parent::__construct($roles);

        if (empty($user)) {
            throw new \InvalidArgumentException('$user must not be empty.');
        }

        $this->setUser($user);
        parent::setAuthenticated(count($roles) > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthenticated($isAuthenticated)
    {
        if ($isAuthenticated) {
            throw new \LogicException('Cannot set this token to trusted after instantiation.');
        }
        parent::setAuthenticated(false);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return '';
    }
}
