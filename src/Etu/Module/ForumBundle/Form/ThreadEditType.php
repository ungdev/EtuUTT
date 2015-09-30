<?php

namespace Etu\Module\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThreadEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add(
                'weight',
                'choice',
                array(
                    'choices' => array(100 => 'Non', 200 => 'Oui'),
                    'multiple' => false,
                    'expanded' => true,
                    'preferred_choices' => array(100),
                    'empty_value' => false,
                    'empty_data' => -1,
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Etu\Module\ForumBundle\Entity\Thread',
            ]
        );
    }

    public function getName()
    {
        return 'etu_module_forumbundle_threadedittype';
    }
}

