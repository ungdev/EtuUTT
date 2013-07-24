<?php

namespace Etu\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BirthdayPickerType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'widget' => 'single_text',
			'format' => 'dd/MM/yyyy',
			'attr' => array('class' => 'date-picker')
		));
	}

	public function getParent()
	{
		return 'date';
	}

	public function getName()
	{
		return 'birthday_picker';
	}
}
