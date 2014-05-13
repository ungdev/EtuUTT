<?php

namespace Etu\Module\CovoitBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProposalStepType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', 'entity', [
                    'label' => 'covoit.proposal_step.city.label',
                    'class' => 'EtuCoreBundle:City',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
                ])
            ->add('adress', 'textarea', ['label' => 'covoit.proposal_step.adress.label'])
            ->add('hour', 'time', ['label' => 'covoit.proposal_step.hour.label'])
            ->add('price', 'integer', ['label' => 'covoit.proposal_step.price.label'])
            ->add('order', 'integer', ['label' => 'covoit.proposal_step.order.label'])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Etu\Module\CovoitBundle\Model\ProposalStep'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'etu_module_covoitbundle_proposal_step';
    }
}
