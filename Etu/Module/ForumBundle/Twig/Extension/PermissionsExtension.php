<?php

namespace Etu\Module\ForumBundle\Twig\Extension;

use Etu\Module\ForumBundle\Entity\Category;
use Etu\Module\ForumBundle\Model\PermissionsChecker;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Twig extension to check user permissions on a page.
 */
class PermissionsExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('forum_can_read', [$this, 'canRead']),
            new \Twig_SimpleFunction('forum_can_post', [$this, 'canPost']),
            new \Twig_SimpleFunction('forum_can_answer', [$this, 'canAnswer']),
            new \Twig_SimpleFunction('forum_can_edit', [$this, 'canEdit']),
            new \Twig_SimpleFunction('forum_can_sticky', [$this, 'canSticky']),
            new \Twig_SimpleFunction('forum_can_lock', [$this, 'canLock']),
            new \Twig_SimpleFunction('forum_can_move', [$this, 'canMove']),
            new \Twig_SimpleFunction('forum_can_delete', [$this, 'canDelete']),
        ];
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function canRead($user, Category $category)
    {
        $checker = new PermissionsChecker($user);

        return $checker->canRead($category);
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function canPost($user, Category $category)
    {
        $checker = new PermissionsChecker($user);

        return $checker->canPost($category);
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function canAnswer($user, Category $category)
    {
        $checker = new PermissionsChecker($user);

        return $checker->canAnswer($category);
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function canEdit($user, Category $category)
    {
        $checker = new PermissionsChecker($user);

        return $checker->canEdit($category);
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function canSticky($user, Category $category)
    {
        $checker = new PermissionsChecker($user);

        return $checker->canSticky($category);
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function canLock($user, Category $category)
    {
        $checker = new PermissionsChecker($user);

        return $checker->canLock($category);
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function canMove($user, Category $category)
    {
        $checker = new PermissionsChecker($user);

        return $checker->canMove($category);
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function canDelete($user, Category $category)
    {
        $checker = new PermissionsChecker($user);

        return $checker->canDelete($category);
    }
}
