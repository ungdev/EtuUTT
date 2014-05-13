<?php

namespace Etu\Module\CovoitBundle\Form;

use Etu\Module\CovoitBundle\Entity\Covoit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProposalType extends AbstractType
{
    /**
     * @var ProposalStepType
     */
    protected $stepType;

    /**
     * @param $stepType
     */
    public function __construct($stepType)
    {
        $this->stepType = $stepType;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', [
                    'label' => 'covoit.proposal.type.label',
                    'choices' => [
                        Covoit::TYPE_SEARCHING => 'covoit.proposal.type.searching',
                        Covoit::TYPE_FINDING => 'covoit.proposal.type.finding',
                    ]]
                )
            ->add('phoneNumber', null, ['label' => 'covoit.proposal.phone.label'])
            ->add('notes', 'textarea', ['required' => false, 'label' => 'covoit.proposal.notes.label'])
            ->add('capacity', 'integer', ['label' => 'covoit.proposal.capacity.label'])
            ->add('date', 'date_picker', ['label' => 'covoit.proposal.date.label'])
            ->add('steps', 'collection', [
                    'label' => 'covoit.proposal.steps.label',
                    'type' => $this->stepType,
                    'allow_add' => true,
                    'allow_delete' => true
                ])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Etu\Module\CovoitBundle\Model\Proposal'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'etu_module_covoitbundle_proposal';
    }
}
