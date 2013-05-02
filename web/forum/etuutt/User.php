<?php

class User
{
	/** @var integer */
	public $UserID;

	/** @var string */
	public $Name;

	/** @var string */
	public $Photo;

	/** @var string */
	public $About;

	/** @var string */
	public $Email;

	/** @var integer */
	public $ShowEmail;

	/** @var string */
	public $Gender;

	/** @var integer */
	public $CountVisits;

	/** @var integer */
	public $CountInvitations;

	/** @var integer */
	public $CountNotifications;

	/** @var integer */
	public $InviteUserID;

	/** @var string */
	public $DiscoveryText;

	/** @var array */
	public $Preferences;

	/** @var array */
	public $Permissions;

	/** @var array */
	public $Attributes;

	/** @var string */
	public $DateSetInvitations;

	/** @var string */
	public $DateOfBirth;

	/** @var string */
	public $DateFirstVisit;

	/** @var string */
	public $DateLastActive;

	/** @var string */
	public $LastIPAddress;

	/** @var string */
	public $DateInserted;

	/** @var string */
	public $InsertIPAddress;

	/** @var string */
	public $DateUpdated;

	/** @var string */
	public $UpdateIPAddress;

	/** @var integer */
	public $HourOffset;

	/** @var integer */
	public $Score;

	/** @var integer */
	public $Admin;

	/** @var integer */
	public $Banned;

	/** @var integer */
	public $Deleted;

	/** @var integer */
	public $CountUnreadConversations;

	/** @var integer */
	public $CountDiscussions;

	/** @var integer */
	public $CountUnreadDiscussions;

	/** @var integer */
	public $CountComments;

	/** @var integer */
	public $CountDrafts;

	/** @var integer */
	public $CountBookmarks;

	/**
	 * @param stdClass $sqlUser
	 * @return User
	 */
	public static function createFromSfUser($sqlUser)
	{
		$user = new self();

		$sex = 'm';

		if ($sqlUser->sex && $sqlUser->sexPrivacy == 100 && $sqlUser->sex = 'female') {
			$sex = 'f';
		}

		$user->UserID = (int) $sqlUser->id;
		$user->Name = $sqlUser->login;
		$user->Photo = $sqlUser->avatar;
		$user->About = null;
		$user->Email = $sqlUser->mail;
		$user->ShowEmail = '1';
		$user->Gender = $sex;
		$user->CountVisits = '1';
		$user->CountInvitations = '0';
		$user->CountNotifications = null;
		$user->InviteUserID = null;
		$user->DiscoveryText = null;
		$user->Preferences = null;
		$user->Permissions = unserialize($sqlUser->permissions);
		$user->Attributes = array('TransientKey' => '');
		$user->DateSetInvitations = null;
		$user->DateOfBirth = '1975-09-16 00:00:00';
		$user->DateFirstVisit = '2013-05-02 07:16:33';
		$user->DateLastActive = date('Y-m-d H:i:s');
		$user->LastIPAddress = '172.16.1.10';
		$user->DateInserted = '2013-05-02 07:16:33';
		$user->InsertIPAddress = null;
		$user->DateUpdated = '2013-05-02 07:16:33';
		$user->UpdateIPAddress = '172.16.1.10';
		$user->HourOffset = '6';
		$user->Score = null;
		$user->Admin = $sqlUser->isAdmin;
		$user->Banned = $sqlUser->isDeleted;
		$user->Deleted = $sqlUser->isDeleted;
		$user->CountUnreadConversations = null;
		$user->CountDiscussions = null;
		$user->CountUnreadDiscussions = null;
		$user->CountComments = null;
		$user->CountDrafts = null;
		$user->CountBookmarks = null;

		return $user;
	}

	/**
	 * @param string $About
	 * @return User
	 */
	public function setAbout($About)
	{
		$this->About = $About;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getAbout()
	{
		return $this->About;
	}

	/**
	 * @param int $Admin
	 * @return User
	 */
	public function setAdmin($Admin)
	{
		$this->Admin = $Admin;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getAdmin()
	{
		return $this->Admin;
	}

	/**
	 * @param array $Attributes
	 * @return User
	 */
	public function setAttributes($Attributes)
	{
		$this->Attributes = $Attributes;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->Attributes;
	}

	/**
	 * @param int $Banned
	 * @return User
	 */
	public function setBanned($Banned)
	{
		$this->Banned = $Banned;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getBanned()
	{
		return $this->Banned;
	}

	/**
	 * @param int $CountBookmarks
	 * @return User
	 */
	public function setCountBookmarks($CountBookmarks)
	{
		$this->CountBookmarks = $CountBookmarks;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountBookmarks()
	{
		return $this->CountBookmarks;
	}

	/**
	 * @param int $CountComments
	 * @return User
	 */
	public function setCountComments($CountComments)
	{
		$this->CountComments = $CountComments;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountComments()
	{
		return $this->CountComments;
	}

	/**
	 * @param int $CountDiscussions
	 * @return User
	 */
	public function setCountDiscussions($CountDiscussions)
	{
		$this->CountDiscussions = $CountDiscussions;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountDiscussions()
	{
		return $this->CountDiscussions;
	}

	/**
	 * @param int $CountDrafts
	 * @return User
	 */
	public function setCountDrafts($CountDrafts)
	{
		$this->CountDrafts = $CountDrafts;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountDrafts()
	{
		return $this->CountDrafts;
	}

	/**
	 * @param int $CountInvitations
	 * @return User
	 */
	public function setCountInvitations($CountInvitations)
	{
		$this->CountInvitations = $CountInvitations;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountInvitations()
	{
		return $this->CountInvitations;
	}

	/**
	 * @param int $CountNotifications
	 * @return User
	 */
	public function setCountNotifications($CountNotifications)
	{
		$this->CountNotifications = $CountNotifications;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountNotifications()
	{
		return $this->CountNotifications;
	}

	/**
	 * @param int $CountUnreadConversations
	 * @return User
	 */
	public function setCountUnreadConversations($CountUnreadConversations)
	{
		$this->CountUnreadConversations = $CountUnreadConversations;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountUnreadConversations()
	{
		return $this->CountUnreadConversations;
	}

	/**
	 * @param int $CountUnreadDiscussions
	 * @return User
	 */
	public function setCountUnreadDiscussions($CountUnreadDiscussions)
	{
		$this->CountUnreadDiscussions = $CountUnreadDiscussions;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountUnreadDiscussions()
	{
		return $this->CountUnreadDiscussions;
	}

	/**
	 * @param int $CountVisits
	 * @return User
	 */
	public function setCountVisits($CountVisits)
	{
		$this->CountVisits = $CountVisits;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getCountVisits()
	{
		return $this->CountVisits;
	}

	/**
	 * @param string $DateFirstVisit
	 * @return User
	 */
	public function setDateFirstVisit($DateFirstVisit)
	{
		$this->DateFirstVisit = $DateFirstVisit;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateFirstVisit()
	{
		return $this->DateFirstVisit;
	}

	/**
	 * @param string $DateInserted
	 * @return User
	 */
	public function setDateInserted($DateInserted)
	{
		$this->DateInserted = $DateInserted;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateInserted()
	{
		return $this->DateInserted;
	}

	/**
	 * @param string $DateLastActive
	 * @return User
	 */
	public function setDateLastActive($DateLastActive)
	{
		$this->DateLastActive = $DateLastActive;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateLastActive()
	{
		return $this->DateLastActive;
	}

	/**
	 * @param string $DateOfBirth
	 * @return User
	 */
	public function setDateOfBirth($DateOfBirth)
	{
		$this->DateOfBirth = $DateOfBirth;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateOfBirth()
	{
		return $this->DateOfBirth;
	}

	/**
	 * @param string $DateSetInvitations
	 * @return User
	 */
	public function setDateSetInvitations($DateSetInvitations)
	{
		$this->DateSetInvitations = $DateSetInvitations;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateSetInvitations()
	{
		return $this->DateSetInvitations;
	}

	/**
	 * @param string $DateUpdated
	 * @return User
	 */
	public function setDateUpdated($DateUpdated)
	{
		$this->DateUpdated = $DateUpdated;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateUpdated()
	{
		return $this->DateUpdated;
	}

	/**
	 * @param int $Deleted
	 * @return User
	 */
	public function setDeleted($Deleted)
	{
		$this->Deleted = $Deleted;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getDeleted()
	{
		return $this->Deleted;
	}

	/**
	 * @param string $DiscoveryText
	 * @return User
	 */
	public function setDiscoveryText($DiscoveryText)
	{
		$this->DiscoveryText = $DiscoveryText;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDiscoveryText()
	{
		return $this->DiscoveryText;
	}

	/**
	 * @param string $Email
	 * @return User
	 */
	public function setEmail($Email)
	{
		$this->Email = $Email;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->Email;
	}

	/**
	 * @param string $Gender
	 * @return User
	 */
	public function setGender($Gender)
	{
		$this->Gender = $Gender;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getGender()
	{
		return $this->Gender;
	}

	/**
	 * @param int $HourOffset
	 * @return User
	 */
	public function setHourOffset($HourOffset)
	{
		$this->HourOffset = $HourOffset;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getHourOffset()
	{
		return $this->HourOffset;
	}

	/**
	 * @param string $InsertIPAddress
	 * @return User
	 */
	public function setInsertIPAddress($InsertIPAddress)
	{
		$this->InsertIPAddress = $InsertIPAddress;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getInsertIPAddress()
	{
		return $this->InsertIPAddress;
	}

	/**
	 * @param int $InviteUserID
	 * @return User
	 */
	public function setInviteUserID($InviteUserID)
	{
		$this->InviteUserID = $InviteUserID;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getInviteUserID()
	{
		return $this->InviteUserID;
	}

	/**
	 * @param string $LastIPAddress
	 * @return User
	 */
	public function setLastIPAddress($LastIPAddress)
	{
		$this->LastIPAddress = $LastIPAddress;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastIPAddress()
	{
		return $this->LastIPAddress;
	}

	/**
	 * @param string $Name
	 * @return User
	 */
	public function setName($Name)
	{
		$this->Name = $Name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->Name;
	}

	/**
	 * @param array $Permissions
	 * @return User
	 */
	public function setPermissions($Permissions)
	{
		$this->Permissions = $Permissions;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getPermissions()
	{
		return $this->Permissions;
	}

	/**
	 * @param string $Photo
	 * @return User
	 */
	public function setPhoto($Photo)
	{
		$this->Photo = $Photo;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPhoto()
	{
		return $this->Photo;
	}

	/**
	 * @param array $Preferences
	 * @return User
	 */
	public function setPreferences($Preferences)
	{
		$this->Preferences = $Preferences;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getPreferences()
	{
		return $this->Preferences;
	}

	/**
	 * @param int $Score
	 * @return User
	 */
	public function setScore($Score)
	{
		$this->Score = $Score;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getScore()
	{
		return $this->Score;
	}

	/**
	 * @param int $ShowEmail
	 * @return User
	 */
	public function setShowEmail($ShowEmail)
	{
		$this->ShowEmail = $ShowEmail;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getShowEmail()
	{
		return $this->ShowEmail;
	}

	/**
	 * @param string $UpdateIPAddress
	 * @return User
	 */
	public function setUpdateIPAddress($UpdateIPAddress)
	{
		$this->UpdateIPAddress = $UpdateIPAddress;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUpdateIPAddress()
	{
		return $this->UpdateIPAddress;
	}

	/**
	 * @param int $UserID
	 * @return User
	 */
	public function setUserID($UserID)
	{
		$this->UserID = $UserID;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getUserID()
	{
		return $this->UserID;
	}
}