<?php

namespace Etu\Module\SIABundle\Ldap\Model;

class User
{
    /**
     * User identifier
     *      => ldap[user][uid].
     *
     * @var string
     */
    protected $login;

    /**
     * Student number
     *      => ldap[user][employeenumber].
     *
     * @var int
     */
    protected $studentId;

    /**
     * E-mail
     *      => ldap[user][mail].
     *
     * @var string
     */
    protected $mail;

    /**
     * First name
     *      => ldap[user][givenname].
     *
     * @var string
     */
    protected $firstName;

    /**
     * Last name
     *      => ldap[user][sn].
     *
     * @var string
     */
    protected $lastName;

    /**
     * Disabled
     *      => ldap[user][nsaccountlock].
     *
     * @var bool
     */
    protected $bloqued;

    /**
     * EtuId
     *      => ldap[user][carlicence].
     *
     * @var bool
     */
    protected $etu_id;

    /**
     * Password
     *      => ldap[user][userpassword].
     *
     * @var string
     */
    protected $userpassword;

    /**
     * EtuUttID.
     *
     * @var int
     */
    protected $etuUttId;

    /**
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $login
     *
     * @return User
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
     * @return User
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
     * @param int $studentId
     *
     * @return User
     */
    public function setStudentId($studentId)
    {
        $this->studentId = $studentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getStudentId()
    {
        return $this->studentId;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setUserPassword($password)
    {
        $this->userpassword = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserPassword()
    {
        return $this->userpassword;
    }

    /**
     * @param int $etuUttId
     *
     * @return User
     */
    public function setEtuUttId($etuUttId)
    {
        $this->etuUttId = $etuUttId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEtuUttId()
    {
        return $this->etuUttId;
    }
}
