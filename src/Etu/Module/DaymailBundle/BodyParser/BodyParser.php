<?php

namespace Etu\Module\DaymailBundle\BodyParser;

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
     * @param $string
     * @return string
     */
    public function parse($string)
    {
        preg_match_all('/<img .+>/isU', $string, $matches);
        $images = $matches[0];

        $imagine = new \Imagine\Gd\Imagine();

        foreach ($images as $image) {
            preg_match('/src="(.+)"/iU', $image, $src);
            $src = $src[1];

            $path = explode('/uploads/', $src);
            $path = $path[1];

            $size = $imagine->open($this->webDir . '/uploads/' . $path)->getSize();

            if ($size->getWidth() > 600) {
                $replacement = '<img src="'. $src .'" style="width:600px; max-width: 100%;" width="600" />';
                $string = str_replace($image, $replacement, $string);
            }
        }

        return $string;
    }
}