<?php

namespace Etu\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditorEmailType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'organization' => null,
            'attr' => function (Options $options) {
                return ['class' => 'editor-email', 'data-organization' => ($options['organization'] ?? null)];
            },
        ]);
    }

    public function getParent()
    {
        return TextareaType::class;
    }
}
