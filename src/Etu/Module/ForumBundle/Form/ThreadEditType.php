<?php

namespace Etu\Module\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThreadEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'forum.main.post.threadTitle'])
            ->add(
                'weight',
                ChoiceType::class,
                [
                    'choices' => ['Non' => 100, 'Oui' => 200],
                    'multiple' => false,
                    'expanded' => true,
                    'preferred_choices' => [100],
                    'placeholder' => null,
                    'empty_data' => -1,
                    'label' => 'forum.main.post.sticky',
                ]
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
}
