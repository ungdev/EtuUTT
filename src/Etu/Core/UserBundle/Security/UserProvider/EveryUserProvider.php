<?php

namespace Etu\Core\UserBundle\Security\UserProvider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Etu\Core\UserBundle\Entity\Organization as OrganizationEntity;
use Etu\Core\UserBundle\Entity\User as UserEntity;
use Etu\Core\UserBundle\Exception\OrganizationNotAuthorizedException;
use Etu\Core\UserBundle\Ldap\LdapManager;
use Etu\Core\UserBundle\Ldap\Model\Organization as LdapOrganization;
use Etu\Core\UserBundle\Ldap\Model\User as ldapUser;
use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToImport;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class EveryUserProvider implements UserProviderInterface
{
    protected $doctrine;
    protected $ldap;

    public function __construct(Registry $doctrine, LdapManager $ldap)
    {
        $this->doctrine = $doctrine;
        $this->ldap = $ldap;
    }

    /**
     * Loads the user or organization for the given username from the database
     * or from LDAP. If an user is only find in LDAP, we import him. If an
     * organization is only find in LDAP we ask them to call an adminstrator.
     *
     * @param string $username The login (CAS or external)
     *
     * @throws UsernameNotFoundException          if the user is not found
     * @throws OrganizationNotAuthorizedException if the organisation is
     *                                            found only on LDAP
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        $em = $this->doctrine->getManager();

        // Try to load user from database
        $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $username]);

        // If user can't be loaded from database, we try for an organization
        if (!$user) {
            $user = $em->getRepository('EtuUserBundle:Organization')->findOneBy(['login' => $username]);
        }

        // If user can't be loaded even as organization, we try using LDAP
        if (!$user) {
            // Try to load user from LDAP
            $ldapUser = $this->ldap->getUser($username);

            // If we can't found any user, try with as an organization
            if (!$ldapUser) {
                $ldapUser = $this->ldap->getOrga($username);
            }

            // We caught a user that is not in the database : we import it !
            if ($ldapUser instanceof ldapUser) {
                $import = new ElementToImport($this->doctrine, $ldapUser);
                $user = $import->import(true);

                if ($user instanceof UserEntity && $user->getDaymail()) {
                    $message = \Swift_Message::newInstance('Daymail subscription')
                       ->setFrom(['ung@utt.fr' => 'UNG'])
                       ->setTo(['sympa@utt.fr'])
                       ->setBody('QUIET ADD daymail '.$user->getMail().' '.$user->getFullName());
                    $result = $mailer->send($message);
                }
            }
            // We caught an organization not in database, we ask them to call and administrator
            elseif ($ldapUser instanceof LdapOrganization) {
                throw new OrganizationNotAuthorizedException(sprintf('Organization "%s" is not authorized.', $username));
            }
        }

        // If we cannot find any user or organization
        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!($user instanceof UserEntity) && !($user instanceof OrganizationEntity)) {
            throw new UnsupportedUserException();
        }

        return $this->loadUserByUsername($user->getLogin());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Etu\Core\UserBundle\Entity\User' || $class === 'Etu\Core\UserBundle\Entity\Organization';
    }
}
