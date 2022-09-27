<?php

namespace Etu\Module\ArgentiqueBundle\Glide\Manipulators;

use Intervention\Image\Image;
use League\Glide\Manipulators\Encode;

/**
 * Customize the original Glide CustomEncode to avoid re-creating the image
 * when converting jpg to jpg to avoid loosing exif informations (exif
 * informations are only conserved when using imagick).
 *
 * @property string $fm
 * @property string $q
 */
class CustomEncode extends Encode
{
    /**
     * Perform output image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image)
    {
        $format = $this->getFormat($image);
        $quality = $this->getQuality();

        // Apply a white background if the source format ($image->mime()) support
        // transparency, while the target format (`$format`)
        // All $allowed format except jpg/pjpg supports transparency
        if ('image/jpeg' != $image->mime() && in_array($format, ['jpg', 'pjpg'], true)) {
            $image = $image->getDriver()
                           ->newImage($image->width(), $image->height(), '#fff')
                           ->insert($image, 'top-left', 0, 0);
        }

        if ('pjpg' === $format) {
            $image->interlace();
            $format = 'jpg';
        }

        return $image->encode($format, $quality);
    }
}
