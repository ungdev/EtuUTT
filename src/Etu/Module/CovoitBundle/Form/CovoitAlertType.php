<?php

namespace Etu\Module\CovoitBundle\Form;

use Doctrine\ORM\EntityRepository;
use Etu\Core\CoreBundle\Form\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CovoitAlertType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DatePickerType::class, ['label' => 'covoit.alerts.label.startDate', 'required' => false])
            ->add('endDate', DatePickerType::class, ['label' => 'covoit.alerts.label.endDate', 'required' => false, 'attr' => ['help' => 'covoit.alerts.create.precise_date']])
            ->add('priceMax', null, ['label' => 'covoit.alerts.label.priceMax', 'required' => false])
            ->add(
                'startCity',
                EntityType::class,
                [
                    'label' => 'covoit.alerts.label.startCity',
                    'required' => false,
                    'class' => 'EtuCoreBundle:City',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
                ]
            )
            ->add(
                'endCity',
                EntityType::class,
                [
                    'label' => 'covoit.alerts.label.endCity',
                    'required' => false,
                    'class' => 'EtuCoreBundle:City',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Etu\Module\CovoitBundle\Entity\CovoitAlert',
            ]
        );
    }
}
