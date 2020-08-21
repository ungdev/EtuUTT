<?php

namespace Etu\Core\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom', 'required' => true])
            ->add('redirectUri', UrlType::class, ['label' => 'URL de redirection', 'required' => true])
            ->add('file', FileType::class, ['label' => 'Image', 'required' => false])
            ->add(
                'scopes',
                null,
                [
                    'label' => 'Vous voulez accéder aux données suivantes :',
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Etu\Core\ApiBundle\Entity\OauthClient',
            ]
        );
    }
}
