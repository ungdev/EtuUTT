<?php

namespace Etu\Module\CovoitBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startCity', 'entity', [
                'label' => 'covoit.search.start_city',
                'required' => false,
                'class' => 'EtuCoreBundle:City',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
            ])
            ->add('endCity', 'entity', [
                'label' => 'covoit.search.end_city',
                'required' => false,
                'class' => 'EtuCoreBundle:City',
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
            ])
            ->add('date', 'date_picker', ['label' => 'covoit.search.date', 'required' => false])
            ->add('dateBeforeAfter', 'checkbox', ['label' => 'covoit.search.date_before_after', 'required' => false])
            ->add('priceMax', null, ['label' => 'covoit.search.price_max', 'required' => false])
            ->add('placesLeft', 'integer', ['label' => 'covoit.search.places_left', 'required' => false])
            ->add('hourMin', 'time', ['label' => 'covoit.search.hour_min', 'minutes' => range(0, 55, 5), 'required' => false])
            ->add('hourMax', 'time', ['label' => 'covoit.search.hour_max', 'minutes' => range(0, 55, 5), 'required' => false])
            ->add('keywords', 'text', ['label' => 'covoit.search.keywords', 'required' => false])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'etu_module_covoitbundle_search';
    }
}
