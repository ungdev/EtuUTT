<?php

namespace Etu\Core\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', ['label' => 'Nom', 'required' => true])
            ->add('redirectUri', 'url', ['label' => 'URL de redirection', 'required' => true])
            ->add('file', 'file', ['label' => 'Image', 'required' => false])
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

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Etu\Core\ApiBundle\Entity\OauthClient',
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'etu_api_client';
    }
}
