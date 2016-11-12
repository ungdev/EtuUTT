<?php

namespace Etu\Module\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Etu\Core\CoreBundle\Form\RedactorType;

class MessageEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', RedactorType::class, ['label' => 'forum.main.post.content'])
            ->add('thread', ThreadEditType::class);
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
