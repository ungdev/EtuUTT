<?php

namespace Etu\Core\ApiBundle\Serializer;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Tga\Api\Common\Collection\ArrayCollection;

class SerializerCollection
{
    /**
     * @var EncoderInterface[]
     */
    protected $encoders;

    /**
     * @var NormalizerInterface[]
     */
    protected $normalizers;

    /**
     * Constructor
     *
     * Remove input access to element
     */
    public function __construct()
    {
        $this->encoders = new ArrayCollection();
        $this->normalizers = new ArrayCollection();
    }

    /**
     * @param EncoderInterface $encoder
     */
    public function addEncoder(EncoderInterface $encoder)
    {
        $this->encoders->add($encoder);
    }

    /**
     * @param NormalizerInterface $normalizer
     */
    public function addNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizers->add($normalizer);
    }

    /**
     * @return \Symfony\Component\Serializer\Encoder\EncoderInterface[]
     */
    public function getEncoders()
    {
        return $this->encoders->all();
    }

    /**
     * @return \Symfony\Component\Serializer\Normalizer\NormalizerInterface[]
     */
    public function getNormalizers()
    {
        return $this->normalizers->all();
    }

    /**
     * @return ArrayCollection
     */
    public function getEncodersCollection()
    {
        return $this->encoders;
    }

    /**
     * @return ArrayCollection
     */
    public function getNormalizersCollection()
    {
        return $this->normalizers;
    }
}
