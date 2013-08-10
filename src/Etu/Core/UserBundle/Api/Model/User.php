<?php

namespace Etu\Core\UserBundle\Api\Model;

// Annotations
use Swagger\Annotations as SWG;

/**
 * @SWG\Model(id="User")
 */
class User
{
	/**
	 * @var integer
	 *
	 * @SWG\Property(
	 *      name="id",
	 *      type="int",
	 *      required="true",
	 *      description="Unique identifier for the user"
	 * )
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="login",
	 *      type="string",
	 *      required="true",
	 *      description="CAS login"
	 * )
	 */
	protected $login;

	/**
	 * @var integer
	 *
	 * @SWG\Property(
	 *      name="studentId",
	 *      type="int",
	 *      required="true",
	 *      description="Student identifier"
	 * )
	 */
	protected $studentId;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="mail",
	 *      type="string",
	 *      required="true",
	 *      description="UTT e-mail"
	 * )
	 */
	protected $mail;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="fullName",
	 *      type="string",
	 *      required="true",
	 *      description="Full name"
	 * )
	 */
	protected $fullName;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="firstName",
	 *      type="string",
	 *      required="true",
	 *      description="First name"
	 * )
	 */
	protected $firstName;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="lastName",
	 *      type="string",
	 *      required="true",
	 *      description="Last name"
	 * )
	 */
	protected $lastName;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="formation",
	 *      type="string",
	 *      required="true",
	 *      description="Formation",
	 *      @SWG\AllowableValues(
	 *          valueType="LIST",
	 *          values="['Ingénieur', 'Master', 'Doctorat']"
	 *      )
	 * )
	 */
	protected $formation;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="branch",
	 *      type="string",
	 *      required="true",
	 *      description="Formation branch",
	 *      @SWG\AllowableValues(
	 *          valueType="LIST",
	 *          values="['Ingénieur', 'Master', 'Doctorat']"
	 *      )
	 * )
	 */
	protected $branch;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="level",
	 *      type="string",
	 *      required="true",
	 *      description="Formation level (1, 2, ...)"
	 * )
	 */
	protected $level;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="speciality",
	 *      type="string",
	 *      required="true",
	 *      description="If user is in branch, his/her speciality"
	 * )
	 */
	protected $speciality;

	/**
	 * @var boolean
	 *
	 * @SWG\Property(
	 *      name="isStudent",
	 *      type="boolean",
	 *      required="true"
	 * )
	 */
	protected $isStudent;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="picture",
	 *      type="string",
	 *      required="true"
	 * )
	 */
	protected $picture;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="website",
	 *      type="string",
	 *      required="true"
	 * )
	 */
	protected $website;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="facebook",
	 *      type="string",
	 *      required="true"
	 * )
	 */
	protected $facebook;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="twitter",
	 *      type="string",
	 *      required="true"
	 * )
	 */
	protected $twitter;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="linkedin",
	 *      type="string",
	 *      required="true"
	 * )
	 */
	protected $linkedin;

	/**
	 * @var string
	 *
	 * @SWG\Property(
	 *      name="viadeo",
	 *      type="string",
	 *      required="true"
	 * )
	 */
	protected $viadeo;

	/**
	 * @param string $branch
	 * @return $this
	 */
	public function setBranch($branch)
	{
		$this->branch = $branch;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getBranch()
	{
		return $this->branch;
	}

	/**
	 * @param string $facebook
	 * @return $this
	 */
	public function setFacebook($facebook)
	{
		$this->facebook = $facebook;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFacebook()
	{
		return $this->facebook;
	}

	/**
	 * @param string $firstName
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @param int $id
	 * @return $this
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param boolean $isStudent
	 * @return $this
	 */
	public function setIsStudent($isStudent)
	{
		$this->isStudent = $isStudent;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIsStudent()
	{
		return $this->isStudent;
	}

	/**
	 * @param string $lastName
	 * @return $this
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
	 * @param string $level
	 * @return $this
	 */
	public function setLevel($level)
	{
		$this->level = $level;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * @param string $linkedin
	 * @return $this
	 */
	public function setLinkedin($linkedin)
	{
		$this->linkedin = $linkedin;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLinkedin()
	{
		return $this->linkedin;
	}

	/**
	 * @param string $login
	 * @return $this
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
	 * @return $this
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
	 * @param string $picture
	 * @return $this
	 */
	public function setPicture($picture)
	{
		$this->picture = $picture;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPicture()
	{
		return $this->picture;
	}

	/**
	 * @param string $speciality
	 * @return $this
	 */
	public function setSpeciality($speciality)
	{
		$this->speciality = $speciality;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSpeciality()
	{
		return $this->speciality;
	}

	/**
	 * @param int $studentId
	 * @return $this
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
	 * @param string $twitter
	 * @return $this
	 */
	public function setTwitter($twitter)
	{
		$this->twitter = $twitter;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTwitter()
	{
		return $this->twitter;
	}

	/**
	 * @param string $viadeo
	 * @return $this
	 */
	public function setViadeo($viadeo)
	{
		$this->viadeo = $viadeo;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getViadeo()
	{
		return $this->viadeo;
	}

	/**
	 * @param string $website
	 * @return $this
	 */
	public function setWebsite($website)
	{
		$this->website = $website;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getWebsite()
	{
		return $this->website;
	}
}
