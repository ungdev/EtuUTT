<?php

namespace Etu\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RedactorType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'redactor'],
        ]);
    }

    public function getParent()
    {
        return TextareaType::class;
    }
}
