<?php

namespace Etu\Module\ArgentiqueBundle\FileManager;

use Artgris\Bundle\FileManagerBundle\Service\CustomConfServiceInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class FileManagerConf implements CustomConfServiceInterface
{
    protected $authorizationChecker;

    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getConf($extra)
    {
        if ($this->authorizationChecker->isGranted('ROLE_ARGENTIQUE_ADMIN')) {
            return [
                'dir' => __DIR__.'/../Resources/photos',
                'type' => 'image',
            ];
        }

        return [];
    }
}
