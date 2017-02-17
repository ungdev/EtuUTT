<?php

namespace Etu\Module\ForumBundle\Form;

use Etu\Core\CoreBundle\Form\EditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', EditorType::class, ['label' => 'forum.main.post.content']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Etu\Module\ForumBundle\Entity\Message',
            ]
        );
    }
}
