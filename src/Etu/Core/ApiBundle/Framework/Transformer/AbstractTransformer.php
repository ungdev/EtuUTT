<?php

namespace Etu\Core\ApiBundle\Framework\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;

abstract class AbstractTransformer
{
    /**
     * @param $object
     * @param EmbedBag $includes
     * @return mixed
     */
    abstract public function transformUnique($object, EmbedBag $includes);

    /**
     * @param $collection
     * @param EmbedBag $includes
     * @return array
     */
    public function transformCollection($collection, EmbedBag $includes)
    {
        $output = [];

        foreach ($collection as $element) {
            $output[] = $this->transform($element, $includes);
        }

        return $output;
    }

    /**
     * @param object|array $input
     * @param EmbedBag $includes
     * @return array
     * @throws \InvalidArgumentException
     */
    public function transform($input, EmbedBag $includes = null)
    {
        if (is_object($input)) {
            return $this->transformUnique($input, ($includes) ? $includes : new EmbedBag());
        } else if (is_array($input)) {
            return $this->transformCollection($input, ($includes) ? $includes : new EmbedBag());
        }

        throw new \InvalidArgumentException();
    }
}