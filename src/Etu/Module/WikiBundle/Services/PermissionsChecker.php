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
     * Check if there is a home page in the category and if user can read it.
     *
     * @param string $category
     *
     * @return bool
     */
    public function canGoHome($category)
    {
        $repo = $this->em->getRepository('EtuModuleWikiBundle:WikiPage');
        $page = $repo->findOneBy([
            'slug' => 'home',
            'category' => $category,
        ], ['createdAt' => 'DESC']);

        if (!$page) {
            return false;
        }

        return $this->canRead($page);
    }

    /**
     * @param Page $page
     *
     * @return bool
     */
    public function canRead(WikiPage $page)
    {
        $organization_id = ($page->getOrganization()) ? $page->getOrganization()->getId() : null;

        return $this->has($page->getReadRight(), $organization_id);
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

        $organization_id = ($page->getOrganization()) ? $page->getOrganization()->getId() : null;

        return $this->has($page->getEditRight(), $organization_id);
    }

    /**
     * @param string $category
     *
     * @return Organization associated to a category
     */
    public function getOrganization($category)
    {
        if (preg_match('/^orga-([a-z-]+)$/', $category, $matches)) {
            // Find organization
            $repo = $this->em->getRepository('EtuUserBundle:Organization');
            $orga = $repo->findOneBy([
                'login' => $matches[1],
            ]);

            return $orga;
        }

        return;
    }

    /**
     * @param string $category
     *
     * @return bool return true if user can crate a page in the category
     */
    public function canCreate($category)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_WIKI_EDIT')) {
            return false;
        }

        // Try to match organization wiki
        $organization = $this->getOrganization($category);
        if ($organization) {
            // Check if user is in organization
            $membership = $this->memberships[$organization->getId()] ?? null;
            if ($membership) {
                return true;
            }
        }

        // Try to match UE wiki
        if (preg_match('/^ue-([a-z0-9]+)$/', $category, $matches)) {
            // Find UE
            $repo = $this->em->getRepository('EtuModuleUVBundle:UV');
            $ue = $repo->findOneBy([
                'code' => $matches[1],
            ]);

            //Check if UE exist
            if ($ue) {
                return true;
            }
        }

        // Try to match general category
        if ($category == 'general') {
            return true;
        }

        return false;
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
        if ($this->authorizationChecker->isGranted('ROLE_WIKI_ADMIN')) {
            return true;
        }

        switch ($right) {
            case WikiPage::RIGHT['ADMIN']:
                return false;
            case WikiPage::RIGHT['ORGA_ADMIN']:
                $membership = $this->membership[$organization_id] ?? null;
                if (count($membership) && $membership->hasPermission('wiki')) {
                    return true;
                }
                break;
            case WikiPage::RIGHT['ORGA_MEMBER']:
                $membership = $this->membership[$organization_id] ?? null;
                if (count($membership)) {
                    return true;
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
