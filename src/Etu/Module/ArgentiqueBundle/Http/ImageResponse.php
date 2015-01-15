<?php

namespace Etu\Module\ArgentiqueBundle\Http;

use Imagine\Gd\Image;
use Symfony\Component\HttpFoundation\Response;

class ImageResponse extends Response
{
    /**
     * @param Image $image
     * @param int $format
     */
    public function __construct(Image $image, $format)
    {
        parent::__construct($image->get($format), 200, [
            'Content-type' => 'image/' . $format
        ]);
    }
}
