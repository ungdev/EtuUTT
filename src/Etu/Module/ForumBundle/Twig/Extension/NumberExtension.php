<?php
namespace Etu\Module\ForumBundle\Twig\Extension;

class NumberExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'ceil' => new \Twig_Filter_Method($this, 'ceil'),
        );
    }

    public function ceil($number)
    {
        return ceil($number);
    }

    public function getName()
    {
        return 'etu.forum_number';
    }
}
?>
