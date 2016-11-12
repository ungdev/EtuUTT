<?php

namespace Etu\Core\UserBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * OrganizationNotAuthorizedException is thrown if a Organization is found on
 * the LDAP but doesn't have account on EtuUTT. An administrator have to create it.
 */
class OrganizationNotAuthorizedException extends AuthenticationException
{
    private $login;

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Organization exist but is not authorized to log in.';
    }
    /**
     * Get the organization login.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }
    /**
     * Set the organization login.
     *
     * @param string $login
     */
    public function setUsername($login)
    {
        $this->login = $login;
    }
    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            $this->login,
            parent::serialize(),
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list($this->login, $parentData) = unserialize($str);
        parent::unserialize($parentData);
    }
    /**
     * {@inheritdoc}
     */
    public function getMessageData()
    {
        return ['{{ login }}' => $this->login];
    }
}
