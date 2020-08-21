<?php

namespace Etu\Core\UserBundle\Sync\Iterator\Element;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Etu\Core\UserBundle\Entity\User;

/**
 * Element to remove from database.
 */
class ElementToRemove
{
    /**
     * @var User
     */
    protected $element;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @throws \RuntimeException
     */
    public function __construct(Registry $doctrine, User $element)
    {
        if (!$element instanceof User) {
            if (is_object($element)) {
                $type = get_class($element);
            } else {
                $type = gettype($element);
            }

            throw new \RuntimeException(sprintf('EtuUTT synchonizer can only remove/keep User objects (%s given)', $type));
        }

        $this->element = $element;
        $this->doctrine = $doctrine;
    }

    /**
     * Remove the user from the database.
     */
    public function remove()
    {
        $user = $this->element;
        $user->addCureentSemesterToHistory();
        $user->setFormation(null);
        $user->setNiveau(null);
        $user->setBranch(null);
        $user->setFiliere(null);
        $user->setUvs(null);
        if ('@utt.fr' == mb_substr($user->getMail(), -7) && !preg_match('/^\.[0-9]{4}$/', mb_substr($user->getMail(), -12, 5))) {
            $user->setMail(null);
        }
        $user->setRoom(null);
        $user->setTitle(null);
        $user->setIsInLDAP(false);
        $user->setDaymail(false);
        if ('03257' === mb_substr($user->getPhoneNumber(), 0, 5)) {
            $user->setPhoneNumber(null);
        }

        // $this->doctrine->getManager()->persist($user);
    }

    /**
     * @return \Etu\Core\UserBundle\Entity\User
     */
    public function getElement()
    {
        return $this->element;
    }
}
