<?php

namespace Etu\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RedactorLimitedType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'attr' => array('class' => 'redactor-limited')
		));
	}

	public function getParent()
	{
		return 'textarea';
	}

	public function getName()
	{
		return 'redactor_limited';
	}
}