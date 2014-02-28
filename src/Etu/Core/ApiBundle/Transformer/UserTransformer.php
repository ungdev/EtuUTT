<?php

namespace Etu\Core\ApiBundle\Transformer;

use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\User;

class UserTransformer extends AbstractTransformer
{
    /**
     * @param object $user
     * @return array
     */
    public function transformUnique($user)
    {
        /** @var User $user */

        return [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'studentId' => $user->getStudentId(),
            'email' => $user->getMail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'fullName' => $user->getFullName(),
            'branch' => $user->getBranch(),
            'level' => $user->getNiveau(),
            'speciality' => $user->getFiliere(),
            'avatar' => $user->getAvatar(),
            'surname' => $user->getSurnom(),
            'website' => $user->getWebsite(),
            'facebook' => $user->getFacebook(),
            'twitter' => $user->getTwitter(),
            'linkedin' => $user->getLinkedin(),
            'viadeo' => $user->getViadeo(),
            'isStudent' => $user->getIsStudent(),
            'bdeMember' => $user->hasActiveMembership(),
        ];
    }
}