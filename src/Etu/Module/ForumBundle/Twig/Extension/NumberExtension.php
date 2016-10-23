<?php

namespace Etu\Module\ForumBundle\Twig\Extension;

class NumberExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'ceil' => new \Twig_SimpleFilter('ceil', [$this, 'ceil']),
        );
    }

    public function ceil($number)
    {
        return ceil($number);
    }
}
