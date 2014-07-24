<?php

namespace Etu\Core\ApiBundle\Form;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthScope;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScopesType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $scopes = $this->em->getRepository('EtuCoreApiBundle:OauthScope')->findBy([], [ 'weight' => 'ASC' ]);

        $choices = [];

        foreach ($scopes as $scope) {
            if ($scope->getScope() != 'public') {
                $choices[$scope->getScope()] = OauthScope::$descDev[$scope->getScope()];
            }
        }

        $resolver->setDefaults([
            'multiple' => true,
            'expanded' => true,
            'choices' => $choices
        ]);
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'scopes';
    }
}
