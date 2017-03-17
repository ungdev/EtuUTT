<?php

namespace Etu\Core\UserBundle\Security\Authentication\Provider;

use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Security\Authentication\Token\CasToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CasProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }

        // Find user login
        $login = '';
        if (is_string($token->getUser())) {
            $login = $token->getUser();
        } elseif ($token->getUser() instanceof User) {
            $login = $token->getUser()->getLogin();
        } else {
            throw new AuthenticationException('Format of user given by token not supported');
        }

        // Renew token
        $user = $this->userProvider->loadUserByUsername($login);

        return new CasToken($user, $user->getRoles());
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof CasToken;
    }
}
