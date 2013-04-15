<?php

namespace Etu\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RedactorType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'attr' => array('class' => 'redactor')
		));
	}

	public function getParent()
	{
		return 'textarea';
	}

	public function getName()
	{
		return 'redactor';
	}
}