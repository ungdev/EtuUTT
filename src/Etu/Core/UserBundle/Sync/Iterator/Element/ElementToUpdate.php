<?php

namespace Etu\Core\UserBundle\Sync\Iterator\Element;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Etu\Core\UserBundle\Entity\User as DbUser;
use Etu\Core\UserBundle\Ldap\Model\User as LdapUser;
use Etu\Core\UserBundle\Model\BadgesManager;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

/**
 * Element to update in database.
 */
class ElementToUpdate
{
    /**
     * @var DbUser
     */
    protected $database;

    /**
     * @var LdapUser
     */
    protected $ldap;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     * @param array    $elements
     *
     * @throws \RuntimeException
     */
    public function __construct(Registry $doctrine, array $elements)
    {
        if (!$elements['ldap'] instanceof LdapUser) {
            if (is_object($elements['ldap'])) {
                $given = get_class($elements['ldap']);
            } else {
                $given = gettype($elements['ldap']);
            }

            throw new \RuntimeException(sprintf(
                'EtuUTT synchonizer can only update User objects (ldap: %s given)', $given
            ));
        }

        if (!$elements['database'] instanceof DbUser) {
            if (is_object($elements['database'])) {
                $given = get_class($elements['database']);
            } else {
                $given = gettype($elements['database']);
            }

            throw new \RuntimeException(sprintf(
                'EtuUTT synchonizer can only update User objects (database: %s given)', $given
            ));
        }

        $this->database = $elements['database'];
        $this->ldap = $elements['ldap'];
        $this->doctrine = $doctrine;
    }

    /**
     * Update the element in the database.
     *
     * @return DbUser
     */
    public function update()
    {
        $persist = false;

        $user = $this->database;
        $history = $user->addCureentSemesterToHistory();

        $niveau = null;
        $branch = $this->ldap->getNiveau();

        preg_match('/^(.+)[0-9]$/i', $this->ldap->getNiveau(), $match);

        if (isset($match[1])) {
            $branch = $match[1];
            $niveau = str_replace($branch, '', $this->ldap->getNiveau());
        }

        // Updates
        if (ucfirst(mb_strtolower($this->ldap->getFormation())) != $this->database->getFormation()) {
            $persist = true;
            $user->setFormation(ucfirst(mb_strtolower($this->ldap->getFormation())));
        }

        if ($niveau != $this->database->getNiveau()) {
            $persist = true;
            $user->setNiveau($niveau);
        }

        if ($branch != $this->database->getBranch()) {
            $persist = true;
            $user->setBranch($branch);
        }

        if ($this->ldap->getFiliere() != $this->database->getFiliere()) {
            $persist = true;
            $user->setFiliere($this->ldap->getFiliere());
        }

        if (implode('|', $this->ldap->getUvs()) != $this->database->getUvs()) {
            $persist = true;
            $user->setUvs(implode('|', $this->ldap->getUvs()));
        }

        if ($this->ldap->getFullName() != $this->database->getFullName()) {
            $persist = true;
            $user->setFullName($this->ldap->getFullName());
        }

        if ($this->ldap->getFirstName() != $this->database->getFirstName()) {
            $persist = true;
            $user->setFirstName($this->ldap->getFirstName());
        }

        if ($this->ldap->getLastName() != $this->database->getLastName()) {
            $persist = true;
            $user->setLastName($this->ldap->getLastName());
        }

        if ($this->ldap->getMail() != $this->database->getMail()) {
            $persist = true;
            $user->setMail($this->ldap->getMail());
        }

        if ($this->ldap->getRoom() != $this->database->getRoom()) {
            $persist = true;
            $user->setRoom($this->ldap->getRoom());
        }

        if ($this->ldap->getStudentId() != $this->database->getStudentId()) {
            $persist = true;
            $user->setStudentId($this->ldap->getStudentId());
        }

        if ($this->ldap->getTitle() != $this->database->getTitle()) {
            $persist = true;
            $user->setTitle($this->ldap->getTitle());
        }

        if ($this->ldap->getIsStudent() != $this->database->getIsStudent()) {
            $persist = true;
            $user->setIsStudent($this->ldap->getIsStudent());
            $user->setIsStaffUTT(!$this->ldap->getIsStudent());
        }

        if (!empty($this->ldap->getPhoneNumber()) && $this->ldap->getPhoneNumber() != 'NC' && $this->ldap->getPhoneNumber() != $this->database->getPhoneNumber()) {
            $persist = true;
            $user->setPhoneNumber($this->ldap->getPhoneNumber());
        }

        if (!$this->database->getIsInLDAP()) {
            $persist = true;
            $user->setIsInLDAP(true);
        }

        // Update official avatar
        $imagine = new Imagine();
        $webDirectory = __DIR__.'/../../../../../../../web';
        $avatar = $this->ldap->getLogin().'_official.jpg';
        try {
            $image = $imagine->open('https://local-sig.utt.fr/Pub/trombi/individu/'.$this->ldap->getStudentId().'.jpg');

            $image->copy()
                ->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND)
                ->save($webDirectory.'/uploads/photos/'.$this->ldap->getLogin().'_official.jpg');
        } catch (\Exception $e) {
            $avatar = 'default-avatar.png';
        }

        if ($this->database->getAvatar() === $avatar || $this->database->getAvatar() === 'default-avatar.png') {
            $persist = true;
            $user->setAvatar($avatar);
        }

        /*
         * Add badges
         */
        if (mb_substr($history['niveau'], 0, 2) == 'TC' && mb_substr($user->getNiveau(), 0, 2) != 'TC') {
            BadgesManager::userAddBadge($user, 'tc_survivor');
            BadgesManager::userPersistBadges($user);
        }

        if ($persist) {
            $this->doctrine->getManager()->persist($user);
        }

        return $persist;
    }

    /**
     * @return \Etu\Core\UserBundle\Ldap\Model\User
     */
    public function getLdapUser()
    {
        return $this->ldap;
    }

    /**
     * @return \Etu\Core\UserBundle\Entity\User
     */
    public function getDatabaseUser()
    {
        return $this->database;
    }
}
