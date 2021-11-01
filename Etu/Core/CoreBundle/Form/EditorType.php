<?php

namespace Etu\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditorType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'organization' => null,
            'attr' => function (Options $options) {
                return ['class' => 'editor', 'data-organization' => ($options['organization'] ?? null)];
            },
        ]);
    }

    public function getParent()
    {
        return TextareaType::class;
    }
}
