<?php

namespace Etu\Core\UserBundle\Ldap\Model;

class Organization
{
    /**
     * Login
     *      => ldap[orga][uid].
     *
     * @var string
     */
    protected $login;

    /**
     * E-mail
     *      => ldap[orga][mail].
     *
     * @var string
     */
    protected $mail;

    /**
     * Full name (first and last name)
     *      => ldap[user][cn].
     *
     * @var string
     */
    protected $fullName;

    /**
     * Is student ?
     *
     * @var bool
     */
    protected $isStudent;

    /**
     * @param string $fullName
     *
     * @return Organization
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $login
     *
     * @return Organization
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $mail
     *
     * @return Organization
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param bool $isStudent
     *
     * @return Organization
     */
    public function setIsStudent($isStudent)
    {
        $this->isStudent = $isStudent;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsStudent()
    {
        return $this->isStudent;
    }
}
