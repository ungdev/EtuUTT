<?php

namespace Etu\Module\ArgentiqueBundle\Glide;

use League\Glide\Factories\UrlBuilder as BuilderFactory;
use League\Glide\UrlBuilder as Builder;
use Symfony\Bundle\TwigBundle\Extension\AssetsExtension;

/**
 * URL builder
 *
 * Used to create photos URLs
 */
class UrlBuilder
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $webPath;

    /**
     * @param $secret
     * @param AssetsExtension $assetsExtension
     */
    public function __construct($secret, AssetsExtension $assetsExtension)
    {
        $this->builder = BuilderFactory::create('', $secret);
        $this->webPath = reset(explode('?', $assetsExtension->getAssetUrl('')));
    }

    /**
     * Generate a photo URL
     *
     * @param string $photo
     * @param array $options
     * @return string
     */
    public function generate($photo, $options = [])
    {
        return $this->webPath . 'argentique/p.php' . $this->builder->getUrl($photo, $options);
    }

    /**
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @param Builder $builder
     */
    public function setBuilder($builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return string
     */
    public function getWebPath()
    {
        return $this->webPath;
    }

    /**
     * @param string $webPath
     */
    public function setWebPath($webPath)
    {
        $this->webPath = $webPath;
    }
}
