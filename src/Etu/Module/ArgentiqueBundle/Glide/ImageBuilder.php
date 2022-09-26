<?php

namespace Etu\Module\ArgentiqueBundle\Glide;

use Etu\Module\ArgentiqueBundle\EtuModuleArgentiqueBundle;
use League\Glide\Responses\SymfonyResponseFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Build configuration to generate image via Glide.
 */
class ImageBuilder
{
    public static function createImageResponse($path, $mode = '')
    {
        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();
        $cache_root = EtuModuleArgentiqueBundle::getPhotosCacheRoot();

        if (!file_exists($root.'/'.$path)) {
            throw new NotFoundHttpException('Picture not found');
        }

        $glide = CustomServerFactory::create(
            [
                'source' => $root,
                'cache' => $cache_root,
                'response' => new SymfonyResponseFactory(),
                'driver' => 'imagick',
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
