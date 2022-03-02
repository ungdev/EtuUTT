<?php

namespace Etu\Module\UploadBundle\Services;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\UploadBundle\Entity\UploadedFile;
use Etu\Module\WikiBundle\Entity\WikiPage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Check permissions for a given user about a given page.
 */
class PermissionsChecker
{
    /**
     * @var User|Organization
     */
    protected $user;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var Member[]
     */
    protected $memberships;

    /**
     * @param User $user
     */
    public function __construct(TokenStorage $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, EntityManager $em)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->authorizationChecker = $authorizationChecker;
        $this->em = $em;

        if (!($this->user instanceof User)) {
            $this->memberships = null;
        } else {
            foreach ($this->user->getMemberships() as $membership) {
                $this->memberships[$membership->getOrganization()->getId()] = $membership;
            }
        }
    }

    /**
     * Check if user has the given right.
     *
     * @param int $right           WikiPage::RIGHT['*']
     * @param int $organization_id
     *
     * @return bool
     */
    public function has($right, $organization_id = null)
    {
        if ($this->authorizationChecker->isGranted('ROLE_WIKI_ADMIN')
            && (null != $organization_id || !in_array($right, [UploadedFile::RIGHT['ORGA_ADMIN'], UploadedFile::RIGHT['ORGA_MEMBER']]))) {
            return true;
        }

        switch ($right) {
            case UploadedFile::RIGHT['ADMIN']:
                return false;
            case UploadedFile::RIGHT['ORGA_ADMIN']:
                $membership = $this->memberships[$organization_id] ?? null;
                if (!is_null($membership) && $membership->hasPermission('wiki')) {
                    return true;
                }
                break;
            case UploadedFile::RIGHT['ORGA_MEMBER']:
                $membership = $this->memberships[$organization_id] ?? null;
                if (!is_null($membership)) {
                    return true;
                }
                break;
            case UploadedFile::RIGHT['STUDENT']:
                if ($this->authorizationChecker->isGranted('ROLE_STUDENT')) {
                    return true;
                }
                break;
            case UploadedFile::RIGHT['ALL']:
                return true;
        }

        return false;
    }

    /**
     * Check if user can upload in an organization or in his personnal directory.
     *
     * @param Organization $organization
     *
     * @return bool
     */
    public function canUpload($organization = null)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_UPLOAD')) {
            return false;
        }

        if (!$organization || $this->has(UploadedFile::RIGHT['ORGA_MEMBER'], $organization->getId()) || $this->has(UploadedFile::RIGHT['ADMIN'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can edit a file.
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function canEdit($file)
    {
        return $this->canDelete($file);
    }

    /**
     * Check if user can delete a file.
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function canDelete($file)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_UPLOAD')) {
            return false;
        }

        if (!$file->getOrganization() || $this->has(UploadedFile::RIGHT['ORGA_ADMIN'], ($file->getOrganization() ? $file->getOrganization()->getId() : null)) || $this->has(UploadedFile::RIGHT['ADMIN'])) {
            return true;
        }
    }

    /**
     * Check if user can read a file.
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function canRead($file)
    {
        return $this->has($file->getReadRight(), ($file->getOrganization() ? $file->getOrganization()->getId() : null)) || $this->has(UploadedFile::RIGHT['ADMIN']);
    }
}
