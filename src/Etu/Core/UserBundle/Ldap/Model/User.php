<?php

namespace Etu\Core\UserBundle\Ldap\Model;

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
     *      => ldap[user][supannempid].
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
     * Full name (first and last name)
     *      => ldap[user][cn].
     *
     * @var string
     */
    protected $fullName;

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
     * Formation
     *      => ldap[user][formation].
     *
     * @var string
     */
    protected $formation;

    /**
     * Level
     *      => ldap[user][niveau].
     *
     * @var string
     */
    protected $niveau;

    /**
     * Filiere
     *      => ldap[user][filiere].
     *
     * @var string
     */
    protected $filiere;

    /**
     * UVs
     *      => ldap[user][uv].
     *
     * @var array
     */
    protected $uvs;

    /**
     * Phone number
     *      => ldap[user][telephonenumber].
     *
     * @var string
     */
    protected $phoneNumber;

    /**
     * Title
     *      => ldap[user][title].
     *
     * @var string
     */
    protected $title;

    /**
     * Room
     *      => ldap[user][roomnumber].
     *
     * @var string
     */
    protected $room;

    /**
     * Photo URL
     *      => ldap[user][employeetype].
     *
     * @var string
     */
    protected $jpegPhoto;

    /**
     * Is a student?
     *      => ldap[user][employeetype].
     *
     * @var bool
     */
    protected $isStudent;

    /**
     * Is a staff ?
     *      => ldap[user][employeetype].
     *
     * @var bool
     */
    protected $isStaffUTT;

    /**
     * @param string $filiere
     *
     * @return User
     */
    public function setFiliere($filiere)
    {
        $this->filiere = $filiere;

        return $this;
    }

    /**
     * @return string
     */
    public function getFiliere()
    {
        return $this->filiere;
    }

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
     * @param string $formation
     *
     * @return User
     */
    public function setFormation($formation)
    {
        $this->formation = $formation;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormation()
    {
        return $this->formation;
    }

    /**
     * @param string $fullName
     *
     * @return User
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
     * @param bool $isStaffUTT
     *
     * @return User
     */
    public function setIsStaffUTT($isStaffUTT)
    {
        $this->isStaffUTT = $isStaffUTT;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsStaffUTT()
    {
        return $this->isStaffUTT;
    }

    /**
     * @param bool $isStudent
     *
     * @return User
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

    /**
     * @param string $jpegPhoto
     *
     * @return User
     */
    public function setJpegPhoto($jpegPhoto)
    {
        $this->jpegPhoto = $jpegPhoto;

        return $this;
    }

    /**
     * @return string
     */
    public function getJpegPhoto()
    {
        return $this->jpegPhoto;
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
     * @param string $niveau
     *
     * @return User
     */
    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * @return string
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * @param string $phoneNumber
     *
     * @return User
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $room
     *
     * @return User
     */
    public function setRoom($room)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoom()
    {
        return $this->room;
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
     * @param string $title
     *
     * @return User
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param array $uvs
     *
     * @return User
     */
    public function setUvs($uvs)
    {
        $this->uvs = $uvs;

        return $this;
    }

    /**
     * @return array
     */
    public function getUvs()
    {
        return $this->uvs;
    }
}
