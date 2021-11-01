<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

/**
 * ArrayManipulationExtension.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class ArrayManipulationExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            'shuffle' => new \Twig_SimpleFilter('shuffle', [$this, 'shuffle']),
        ];
    }

    /**
     * Shuffle an array.
     *
     * @return array
     */
    public static function shuffle(array $array)
    {
        shuffle($array);

        return $array;
    }
}
