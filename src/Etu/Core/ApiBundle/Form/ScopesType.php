<?php

namespace Etu\Core\ApiBundle\Form;

use Doctrine\ORM\EntityManager;
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

        $devsDescs = [
            'private_user_account' => 'De lire les données privées du compte de l\'utilisateur connecté',
            'private_user_schedule' => 'De lire l\'emploi du temps de l\'utilisateur connecté',
            'private_user_organizations' => 'De lire les données associatives de l\'utilisateur connecté',
        ];

        foreach ($scopes as $scope) {
            if ($scope->getScope() != 'public') {
                $choices[$scope->getScope()] = $devsDescs[$scope->getScope()];
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
