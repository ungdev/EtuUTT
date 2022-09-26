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
    /**
     * Create glide image generator server.
     *
     * @param mixed $path
     *
     * @return League\Glide\Server the glide server
     */
    protected static function getGlideServer($path)
    {
        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();
        $cache_root = EtuModuleArgentiqueBundle::getPhotosCacheRoot();

        if (!file_exists($root.'/'.$path)) {
            throw new NotFoundHttpException('Picture not found');
        }

        return CustomServerFactory::create(
            [
                'source' => $root,
                'cache' => $cache_root,
                'response' => new SymfonyResponseFactory(),
                'driver' => 'imagick',
            ]
        );
    }

    /**
     * Create an image response for the given mode. Image will be pulled from
     * cache or created if needed.
     *
     * @param string $path Image path
     * @param string $mode Generation mode between 'thumbnail', 'slideshow' or empty
     *
     * @return Reponse The image reponse
     */
    public static function createImageResponse($path, $mode = '')
    {
        $glideServer = ImageBuilder::getGlideServer($path);

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

        return $glideServer->getImageResponse($path, $param);
    }

    /**
     * Clear all caches (all modes) for the given image path.
     *
     * @param $path Image path
     */
    public static function deleteCache($path)
    {
        $glideServer = ImageBuilder::getGlideServer($path);
        $glideServer->deleteCache($path);
    }
}
