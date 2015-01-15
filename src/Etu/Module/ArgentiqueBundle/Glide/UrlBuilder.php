<?php

namespace Etu\Module\ArgentiqueBundle\Glide;

use League\Glide\Factories\UrlBuilder as BuilderFactory;
use League\Glide\UrlBuilder as Builder;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param ContainerInterface $container
     */
    public function __construct($secret, ContainerInterface $container)
    {
        $this->builder = BuilderFactory::create('', $secret);

        if ($container->has('twig.extension.assets')) {
            $this->webPath = reset(explode('?', $container->get('twig.extension.assets')->getAssetUrl('')));
        } else {
            $this->webPath = '';
        }
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
        return $this->webPath . '/argentique/p.php' . $this->builder->getUrl($photo, $options);
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
