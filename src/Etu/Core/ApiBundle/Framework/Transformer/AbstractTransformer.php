<?php

namespace Etu\Core\ApiBundle\Framework\Transformer;

abstract class AbstractTransformer
{
    /**
     * @param object $object
     * @return array
     */
    abstract public function transformUnique($object);

    /**
     * @param $collection
     * @return array
     */
    public function transformCollection($collection)
    {
        $output = [];

        foreach ($collection as $element) {
            $output[] = $this->transform($element);
        }

        return $output;
    }

    /**
     * @param $input
     * @return array
     * @throws \InvalidArgumentException
     */
    public function transform($input)
    {
        if (is_object($input)) {
            return $this->transformUnique($input);
        } else if (is_array($input)) {
            return $this->transformCollection($input);
        }

        throw new \InvalidArgumentException();
    }
}