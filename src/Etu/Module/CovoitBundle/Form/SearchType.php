<?php

namespace Etu\Module\CovoitBundle\Form;

use Doctrine\ORM\EntityRepository;
use Etu\Core\CoreBundle\Form\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('get')
            ->add('startCity', EntityType::class, [
                'label' => 'covoit.search.start_city',
                'required' => false,
                'class' => 'EtuCoreBundle:City',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
            ])
            ->add('endCity', EntityType::class, [
                'label' => 'covoit.search.end_city',
                'required' => false,
                'class' => 'EtuCoreBundle:City',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
            ])
            ->add('date', DatePickerType::class, ['label' => 'covoit.search.date', 'required' => false])
            ->add('dateBeforeAfter', CheckboxType::class, ['label' => 'covoit.search.date_before_after', 'required' => false])
            ->add('priceMax', null, ['label' => 'covoit.search.price_max', 'required' => false])
            ->add('placesLeft', IntegerType::class, ['label' => 'covoit.search.places_left', 'required' => false])
            ->add('hourMin', TimeType::class, ['label' => 'covoit.search.hour_min', 'minutes' => range(0, 55, 5), 'required' => false])
            ->add('hourMax', TimeType::class, ['label' => 'covoit.search.hour_max', 'minutes' => range(0, 55, 5), 'required' => false])
            ->add('keywords', TextType::class, ['label' => 'covoit.search.keywords', 'required' => false])
            ->add('olds', CheckboxType::class, ['label' => 'covoit.search.olds', 'required' => false]);
    }
}
