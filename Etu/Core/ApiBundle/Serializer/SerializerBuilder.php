<?php

namespace Etu\Core\ApiBundle\Serializer;

use Symfony\Component\Serializer\Serializer;

class SerializerBuilder
{
    /**
     * @var SerializerCollection
     */
    protected $collection;

    public function __construct(SerializerCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return Serializer
     */
    public function createSerializer()
    {
        return new Serializer($this->collection->getNormalizers(), $this->collection->getEncoders());
    }
}
