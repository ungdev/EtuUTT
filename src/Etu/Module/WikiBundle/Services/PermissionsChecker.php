<?php

namespace Etu\Module\WikiBundle\Services;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Entity\Member;
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
     * @param User                          $user
     * @param TokenStorage                  $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityManager                 $em
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
     * Give the home page slug for the given organization (or general wiki if null) if configured
     * If not configured or cannot be read by user, it returns null.
     *
     * @param Organization $organization
     *
     * @return string slug or nul
     */
    public function getHomeSlug(Organization $organization = null)
    {
        // Find homepage name
        $homepageSlug = $organization->getWikiHomepage();

        // Check if page exist
        $repo = $this->em->getRepository('EtuModuleWikiBundle:WikiPage');
        $page = $repo->findOneBy([
            'slug' => $homepageSlug,
            'organization' => $organization,
        ], ['createdAt' => 'DESC']);

        if (!$page || $page->isDeleted() || !$this->canRead($page)) {
            return false;
        }

        return $homepageSlug;
    }

    /**
     * @param Page $page
     *
     * @return bool
     */
    public function canSetHome(Organization $organization = null)
    {
        return $this->has(WikiPage::RIGHT['ORGA_ADMIN'], $organization);
    }

    /**
     * @param Page $page
     *
     * @return bool
     */
    public function canRead(WikiPage $page)
    {
        return $this->has($page->getReadRight(), $page->getOrganization());
    }

    /**
     * @param Page $page
     *
     * @return bool
     */
    public function canEdit(WikiPage $page)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_WIKI_EDIT')) {
            return false;
        }

        return $this->has($page->getEditRight(), $page->getOrganization());
    }

    /**
     * @param Organization $organization
     *
     * @return bool return true if user can crate a page in the category
     */
    public function canCreate(Organization $organization = null)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_WIKI_EDIT')) {
            return false;
        }

        // For organization wiki
        if ($organization) {
            // Check if user is in organization
            $membership = $this->memberships[$organization->getId()] ?? null;
            if ($membership || $this->authorizationChecker->isGranted('ROLE_WIKI_ADMIN')) {
                return true;
            }
        }

        // For general wiki
        return true;
    }

    /**
     * Check if user has the given right.
     *
     * @param int $right           WikiPage::RIGHT['*']
      * @param Organization $organization
     *
     * @return bool
     */
    public function has($right, Organization $organization = null)
    {
        if ($this->authorizationChecker->isGranted('ROLE_WIKI_ADMIN')
            && ($organization != null || !in_array($right, [WikiPage::RIGHT['ORGA_ADMIN'], WikiPage::RIGHT['ORGA_MEMBER']]))) {
            return true;
        }

        switch ($right) {
            case WikiPage::RIGHT['ADMIN']:
                return false;
            case WikiPage::RIGHT['ORGA_ADMIN']:
                if($organization) {
                    $membership = $this->memberships[$organization->getId()] ?? null;
                    if ($organization && count($membership) && $membership->hasPermission('wiki')) {
                        return true;
                    }
                }
                break;
            case WikiPage::RIGHT['ORGA_MEMBER']:
                if($organization) {
                    $membership = $this->memberships[$organization->getId()] ?? null;
                    if (count($membership)) {
                        return true;
                    }
                }
                break;
            case WikiPage::RIGHT['STUDENT']:
                if ($this->authorizationChecker->isGranted('ROLE_STUDENT')) {
                    return true;
                }
                break;
            case WikiPage::RIGHT['ALL']:
                return true;
        }

        return false;
    }
}
