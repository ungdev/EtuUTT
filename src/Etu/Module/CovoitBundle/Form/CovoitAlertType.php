<?php

namespace Etu\Module\CovoitBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CovoitAlertType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', 'date_picker', ['label' => 'covoit.alerts.label.startDate', 'required' => false])
            ->add('endDate', 'date_picker', ['label' => 'covoit.alerts.label.endDate', 'required' => false])
            ->add('priceMax', null, ['label' => 'covoit.alerts.label.priceMax', 'required' => false])
            ->add('startCity', 'entity', [
                'label' => 'covoit.alerts.label.startCity',
                'required' => false,
                'class' => 'EtuCoreBundle:City',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
            ])
            ->add('endCity', 'entity', [
                'label' => 'covoit.alerts.label.endCity',
                'required' => false,
                'class' => 'EtuCoreBundle:City',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
            ])
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Etu\Module\CovoitBundle\Entity\CovoitAlert'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'etu_module_covoitbundle_covoitalert';
    }
}
