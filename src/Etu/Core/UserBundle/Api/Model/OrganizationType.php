<?php

namespace Etu\Core\UserBundle\Api\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class OrganizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', TextType::class)
            ->add('name', TextType::class)
            ->add('mail', TextType::class)
            ->add('phone', TextType::class)
            ->add('description', TextType::class)
            ->add('descriptionShort', TextType::class)
            ->add('website', TextType::class);
    }
}
