<?php

namespace Etu\Module\CovoitBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CovoitType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phoneNumber', null, ['label' => 'covoit.proposal.phone.label'])
            ->add('notes', 'redactor_limited', ['label' => 'covoit.proposal.notes.label'])
            ->add('capacity', null, ['label' => 'covoit.proposal.capacity.label'])
            ->add('date', 'date_picker', ['label' => 'covoit.proposal.date.label'])
            ->add('price', null, ['label' => 'covoit.proposal.price.label'])
            ->add('blablacarUrl', null, ['label' => 'covoit.proposal.blablacarUrl.label'])
            ->add('startCity', 'entity', [
                'label' => 'covoit.proposal.start.city.label',
                'class' => 'EtuCoreBundle:City',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
            ])
            ->add('startAdress', 'textarea', ['label' => 'covoit.proposal.start.adress.label'])
            ->add('startHour', 'time', ['label' => 'covoit.proposal.start.hour.label', 'minutes' => range(0, 55, 5)])
            ->add('endCity', 'entity', [
                'label' => 'covoit.proposal.end.city.label',
                'class' => 'EtuCoreBundle:City',
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
            ])
            ->add('endAdress', 'textarea', ['label' => 'covoit.proposal.end.adress.label'])
            ->add('endHour', 'time', ['label' => 'covoit.proposal.end.hour.label', 'minutes' => range(0, 55, 5)])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Etu\Module\CovoitBundle\Entity\Covoit'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'etu_module_covoitbundle_covoit';
    }
}
