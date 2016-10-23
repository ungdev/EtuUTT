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
        return array(
            'shuffle' => new \Twig_SimpleFilter('shuffle', [$this, 'shuffle']),
        );
    }

    /**
     * Shuffle an array.
     *
     * @param array $array
     *
     * @return array
     */
    public static function shuffle(array $array)
    {
        shuffle($array);

        return $array;
    }
}
