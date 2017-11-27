<?php

namespace Etu\Module\ArgentiqueBundle\Glide;

use Etu\Module\ArgentiqueBundle\EtuModuleArgentiqueBundle;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\ServerFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Build configuration to generate image via Glide.
 */
class ImageBuilder
{
    public static function createImageResponse($container, $path, $mode = '')
    {
        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();
        $cache_root = $container->getParameter('kernel.cache_dir').'/argentique/';

        if (!file_exists($root.'/'.$path)) {
            throw new NotFoundHttpException('Picture not found');
        }

        $glide = ServerFactory::create(
            [
                'source' => $root,
                'cache' => $cache_root,
                'response' => new SymfonyResponseFactory(),
            ]
        );

        switch ($mode) {
            case 'thumbnail':
                $param = ['h' => 240, 'q' => 30, 'fm' => 'pjpg'];
                break;
            case 'slideshow':
                $param = ['w' => 1920, 'h' => 1080, 'q' => 50, 'fm' => 'pjpg'];
                break;
            default:
                $param = ['q' => 90, 'fm' => 'pjpg'];
        }

        return $glide->getImageResponse($path, $param);
    }
}
