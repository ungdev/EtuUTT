<?php

namespace Etu\Module\SIABundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class createAccount extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, [
            'disabled' => true,
            'required' => false,
            'label' => "Nom d'utilisateur",
        ])
            ->add('plainPassword', RepeatedType::class, [
                'required' => true,
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Mot de passe',
                ],
                'second_options' => [
                    'label' => 'Confirmation de ton mot de passe',
                ],
            ])
            ->add('termsAccepted', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => new IsTrue(),
                'label' => "J'accepte les conditions d'utilisation",
            ])
            ->add('infoTransfert', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => new IsTrue(),
                'label' => "J'accepte la transmission de certaines de mes informations personnelles, nécessaires à la création de mon compte, au SIA. Ceci inclut: nom, prénom, adresse mail et numéro étudiant",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getBlockPrefix()
    {
        return 'etu_module_siabundlecreate_account';
    }
}
