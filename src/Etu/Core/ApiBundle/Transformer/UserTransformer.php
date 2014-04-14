<?php

namespace Etu\Core\ApiBundle\Transformer;

use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class UserTransformer extends AbstractTransformer
{
    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param object $user
     * @return array
     */
    public function transformUnique($user)
    {
        /** @var User $user */

        $root = $this->router->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return [
            'login' => $user->getLogin(),
            'studentId' => $user->getStudentId(),
            'email' => $user->getMail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'fullName' => $user->getFullName(),
            'branch' => $user->getBranch(),
            'level' => $user->getNiveau(),
            'speciality' => $user->getFiliere(),
            'surname' => $user->getSurnom(),
            'website' => $user->getWebsite(),
            'facebook' => $user->getFacebook(),
            'twitter' => $user->getTwitter(),
            'linkedin' => $user->getLinkedin(),
            'viadeo' => $user->getViadeo(),
            'isStudent' => $user->getIsStudent(),
            'bdeMember' => $user->hasActiveMembership(),
            'image' => [
                'official' => $root . 'photos/'.$user->getLogin().'_official.jpg',
                'custom' => $root . 'photos/'.$user->getAvatar(),
            ],
        ];
    }
}