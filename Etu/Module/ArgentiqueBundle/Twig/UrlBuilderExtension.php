<?php

namespace Etu\Module\ArgentiqueBundle\Twig;

use Etu\Module\ArgentiqueBundle\Glide\UrlBuilder;

/**
 * Twig extension to compare privacies and to fetch them.
 */
class UrlBuilderExtension extends \Twig_Extension
{
    /**
     * @var UrlBuilder
     */
    protected $builder;

    public function __construct(UrlBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('argentique_photo', [$this, 'generatePhotoUrl']),
            new \Twig_SimpleFunction('argentique_collection', [$this, 'generateCollectionUrl']),
        ];
    }

    /**
     * @param string $path
     * @param array  $options
     *
     * @return string
     */
    public function generatePhotoUrl($path, $options = [])
    {
        return $this->builder->generate($path, $options);
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function generateCollectionUrl($path)
    {
        return $this->builder->getWebPath().'/argentique/directory/'.$path;
    }
}
