<?php

namespace Etu\Module\DaymailBundle\BodyParser;

use Imagine\Gd\Imagine;
use Symfony\Component\HttpKernel\Kernel;

class BodyParser
{
    /**
     * @var string
     */
    protected $webDir;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->webDir = $kernel->getRootDir().'/../web';
    }

    /**
     * @param $html
     *
     * @return html
     */
    public function parse($html)
    {
        preg_match_all('/<img .+>/isU', $html, $matches);
        $images = $matches[0];

        $imagine = new Imagine();

        foreach ($images as $image) {
            preg_match('/style="(.+)"/iU', $image, $style);
            $style = ($style) ? $style[1] : '';

            preg_match('/src="(.+)"/iU', $image, $src);
            preg_match('/data-width="([0-9]+)"/iU', $image, $width);
            preg_match('/data-height="([0-9]+)"/iU', $image, $height);

            if ($width && $height && $src) {
                // Resize image if necessary
                $width = $width[1];
                $height = $height[1];
                $src = $src[1];

                if ($width > 600) {
                    $height = 600 * $height / $width;
                }
                if ($height > 500) {
                    $width = 500 * $width / $height;
                }

                $html = str_replace($image, '<img src="'.$src.'" style="'.$style.';width:100%; max-width:'.$width.'px; height:auto;" width="'.$width.'" data-width="'.$width.'" data-height="'.$height.'" />', $html);
            } elseif ($src) {
                $src = $src[1];
                // Fallback on a 250x250px image
                $html = str_replace($image, '<img src="'.$src.'" style="'.$style.';width:250px; height: 250px;" width="250" height="250" />', $html);
            } else {
                // Remove image if we cannot find src field
                $html = str_replace($image, '', $html);
            }
        }

        return $html;
    }
}
