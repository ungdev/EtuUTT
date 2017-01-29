<?php

namespace Etu\Module\CovoitBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Etu\Core\CoreBundle\Form\DatePickerType;
use Etu\Core\CoreBundle\Form\EditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CovoitType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phoneNumber', null, ['label' => 'covoit.proposal.phone.label'])
            ->add('notes', EditorType::class, ['required' => false, 'label' => 'covoit.proposal.notes.label'])
            ->add('capacity', null, ['label' => 'covoit.proposal.capacity.label'])
            ->add('date', DatePickerType::class, ['label' => 'covoit.proposal.date.label'])
            ->add('price', null, ['label' => 'covoit.proposal.price.label'])
            ->add('blablacarUrl', null, ['label' => 'covoit.proposal.blablacarUrl.label'])
            ->add(
                'startCity',
                EntityType::class,
                [
                    'label' => 'covoit.proposal.start.city.label',
                    'class' => 'EtuCoreBundle:City',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
                ]
            )
            ->add('startAdress', TextareaType::class, ['label' => 'covoit.proposal.start.adress.label'])
            ->add('startHour', TimeType::class, ['label' => 'covoit.proposal.start.hour.label', 'minutes' => range(0, 55, 5)])
            ->add(
                'endCity',
                EntityType::class,
                [
                    'label' => 'covoit.proposal.end.city.label',
                    'class' => 'EtuCoreBundle:City',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
                ]
            )
            ->add('endAdress', TextareaType::class, ['label' => 'covoit.proposal.end.adress.label'])
            ->add('endHour', TimeType::class, ['label' => 'covoit.proposal.end.hour.label', 'minutes' => range(0, 55, 5)]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Etu\Module\CovoitBundle\Entity\Covoit',
            ]
        );
    }
}
