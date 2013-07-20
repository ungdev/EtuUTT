<?php

namespace Etu\Module\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Etu\Module\ForumBundle\Form\MessageType;

class ThreadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('weight', 'choice', array('choices' => array(100 => 'Non', 200 => 'Oui'),
			'multiple' => false,
			'expanded' => true,
			'preferred_choices' => array(100),
			'empty_value' => false,
			'empty_data'  => -1))
            ->add('lastMessage', new MessageType())
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Etu\Module\ForumBundle\Entity\Thread'
        ));
    }

    public function getName()
    {
        return 'etu_module_forumbundle_threadtype';
    }
}
