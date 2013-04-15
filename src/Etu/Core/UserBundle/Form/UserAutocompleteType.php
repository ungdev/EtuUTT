<?php

namespace Etu\Core\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserAutocompleteType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'attr' => array('class' => 'user-autocomplete')
		));
	}

	public function getParent()
	{
		return 'text';
	}

	public function getName()
	{
		return 'user';
	}
}