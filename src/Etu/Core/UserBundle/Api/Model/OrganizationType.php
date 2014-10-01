<?php

namespace Etu\Core\UserBundle\Api\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OrganizationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', 'text')
            ->add('name', 'text')
            ->add('mail', 'text')
            ->add('phone', 'text')
            ->add('description', 'text')
            ->add('descriptionShort', 'text')
            ->add('website', 'text')
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'etu_api_model_organization';
    }
}
