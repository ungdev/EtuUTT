<?php

namespace Etu\Module\CovoitBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CovoitStepType extends AbstractType
{
    /**
     * @var bool
     */
    protected $isFirstStep;

    /**
     * @param bool $isFirstStep
     */
    public function __construct($isFirstStep = false)
    {
        $this->isFirstStep = $isFirstStep;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', null, ['label' => 'covoit.proposal_step.city.label'])
            ->add('adress', 'textarea', ['label' => 'covoit.proposal_step.adress.label'])
            ->add('hour', 'time', ['label' => 'covoit.proposal_step.hour.label'])
        ;

        if (! $this->isFirstStep) {
            $builder
                ->add('price', null, ['label' => 'covoit.proposal_step.price.label'])
                ->add('order', 'hidden')
            ;
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Etu\Module\CovoitBundle\Entity\CovoitStep'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'etu_module_covoitbundle_covoitstep';
    }
}
