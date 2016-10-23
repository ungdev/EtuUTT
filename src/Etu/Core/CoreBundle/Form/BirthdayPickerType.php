<?php

namespace Etu\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BirthdayPickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'attr' => array('class' => 'birthday-picker'),
        ));
    }

    public function getParent()
    {
        return 'date';
    }
}
