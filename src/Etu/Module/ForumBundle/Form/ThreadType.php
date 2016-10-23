<?php

namespace Etu\Module\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThreadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
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
            )
            ->add('lastMessage', new MessageType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Etu\Module\ForumBundle\Entity\Thread',
            ]
        );
    }
}
