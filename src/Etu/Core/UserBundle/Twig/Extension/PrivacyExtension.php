<?php

namespace Etu\Core\UserBundle\Twig\Extension;

use Etu\Core\UserBundle\Entity\User;

/**
 * Twig extension to compare privacies and to fetch them.
 */
class PrivacyExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_private', [$this, 'isPrivate']),
            new \Twig_SimpleFunction('is_public', [$this, 'isPublic']),
        ];
    }

    /**
     * Check if the given privacy is private.
     *
     * @param $privacy
     *
     * @return bool
     */
    public function isPrivate($privacy)
    {
        return User::PRIVACY_PRIVATE == $privacy;
    }

    /**
     * Check if the given privacy is public.
     *
     * @param $privacy
     *
     * @return bool
     */
    public function isPublic($privacy)
    {
        return User::PRIVACY_PUBLIC == $privacy;
    }
}
