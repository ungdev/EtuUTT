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

    protected function array_different($array1, $array2)
    {
        if(is_null($array1) !== is_null($array2)) {
            return True;
        }
        return $array1 !== $array2;
    }

    /**
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

            throw new \RuntimeException(sprintf('EtuUTT synchonizer can only update User objects (ldap: %s given)', $given));
        }

        BadgesManager::setDoctrine($doctrine->getEntityManager());

        if (!$elements['database'] instanceof DbUser) {
            if (is_object($elements['database'])) {
                $given = get_class($elements['database']);
            } else {
                $given = gettype($elements['database']);
            }

            throw new \RuntimeException(sprintf('EtuUTT synchonizer can only update User objects (database: %s given)', $given));
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

        $level = null;
        $branch = $this->ldap->getNiveau();

        preg_match('/^(.+)[0-9]$/i', $this->ldap->getNiveau(), $match);

        if (isset($match[1])) {
            $branch = $match[1];
            $level = str_replace($branch, '', $this->ldap->getNiveau());
        }

        $branchList = [];
        $niveauList = [];
        foreach ($this->ldap->getNiveauList() as $niveau) {
            preg_match('/^(.+)[0-9]$/i', $niveau, $match);
            if (isset($match[1])) {
                $branchList[] = $match[1];
                $niveauList[] = str_replace($match[1], '', $niveau);
            }
        }

        // Updates
        if (ucfirst(mb_strtolower($this->ldap->getFormation())) != $this->database->getFormation()) {
            $persist = true;
            $user->setFormation(ucfirst(mb_strtolower($this->ldap->getFormation())));
        }

        if ($this->array_different($this->ldap->getFormationList(), $this->database->getFormationList())) {
            $persist = true;
            $user->setFormationList($this->ldap->getFormationList());
        }

        if ($this->array_different($this->ldap->getNiveauList(), $this->database->getBranchNiveauList())) {
            $persist = true;
            $user->setBranchNiveauList($this->ldap->getNiveauList());
        }

        if ($level != $this->database->getNiveau()) {
            $persist = true;
            $user->setNiveau($level);
        }

        if ($branch != $this->database->getBranch()) {
            $persist = true;
            $user->setBranch($branch);
        }

        // On remet les branches et niveaux dans l'ordre
        if(count($this->ldap->getFormationList()) == count($branchList)) {
            for ($position = 0; $position < count($branchList); $position++) {
                $branch = $branchList[$position];
                if(array_key_exists($branch, DbUser::$branchToFormation) && in_array(DbUser::$branchToFormation[$branch], $this->ldap->getFormationList())) {
                    $positionInBranch = array_search(DbUser::$branchToFormation[$branch], $this->ldap->getFormationList());
                    if($position != $positionInBranch) {
                        $temp = $branchList[$positionInBranch];
                        $temp2 = $niveauList[$positionInBranch];
                        $branchList[$positionInBranch] = $branch;
                        $niveauList[$positionInBranch] = $niveauList[$position];
                        $branchList[$position] = $temp;
                        $niveauList[$position] = $temp2;
                    }
                }
            }
        }

        // On remet les filieres dans l'ordre
        $filiereList = $this->ldap->getFiliereList();
        if(count($filiereList) == count($branchList)) {
            for ($position = 0; $position < count($filiereList); $position++) {
                $filiere = $filiereList[$position];
                if(array_key_exists($filiere, DbUser::$filieresToBranch) && in_array(DbUser::$filieresToBranch[$filiere], $branchList)) {
                    $positionInBranch = array_search(DbUser::$filieresToBranch[$filiere], $branchList);
                    if($position != $positionInBranch) {
                        $temp = $filiereList[$positionInBranch];
                        $filiereList[$positionInBranch] = $filiere;
                        $filiereList[$position] = $temp;
                    }
                }
            }
        }

        if ($this->ldap->getFiliere() !== $this->database->getFiliere()) {
            $persist = true;
            $user->setFiliere($this->ldap->getFiliere());
        }

        if ($this->array_different($filiereList, $this->database->getFiliereList())) {
            $persist = true;
            $user->setFiliereList($filiereList);
        }

        if($this->array_different($branchList, $this->database->getBranchList())) {
            $persist = true;
            $user->setBranchList($branchList);
        }

        if ($this->array_different($niveauList, $this->database->getNiveauList())) {
            $persist = true;
            $user->setNiveauList($niveauList);
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
        }

        if ($this->ldap->getIsStaffUTT() != $this->database->getIsStaffUTT()) {
            $persist = true;
            $user->setIsStaffUTT($this->ldap->getIsStaffUTT());
        }

        if (!empty($this->ldap->getPhoneNumber()) && 'NC' != $this->ldap->getPhoneNumber() && $this->ldap->getPhoneNumber() != $this->database->getPhoneNumber()) {
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
        $profilePicturesDirectory = __DIR__.'/../../../../../../../profilePictures';
        $avatar = $this->ldap->getLogin().'_official.jpg';
        try {
            $image = $imagine->open($profilePicturesDirectory.'/'.$this->ldap->getStudentId().'.jpg');

            $image->copy()
                ->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND)
                ->save($webDirectory.'/uploads/photos/'.$this->ldap->getLogin().'_official.jpg');
        } catch (\Exception $e) {
            $avatar = 'default-avatar.png';
        }

        if ($this->database->getAvatar() === $avatar || 'default-avatar.png' === $this->database->getAvatar()) {
            $persist = true;
            $user->setAvatar($avatar);
        }

        /*
         * Add badges
         */
        if ('TC' == mb_substr($history['niveau'], 0, 2) && 'TC' != mb_substr($user->getNiveau(), 0, 2)) {
            BadgesManager::userAddBadge($user, 'tc_survivor');
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
