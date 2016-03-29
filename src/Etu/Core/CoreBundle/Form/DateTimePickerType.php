<?php

namespace Etu\Core\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateTimePickerType extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'date_widget' => 'single_text',
			'date_format' => 'dd/MM/yyyy',
			'time_widget' => 'single_text',
			'attr' => array('class' => 'datetime-picker')
		));
	}

	public function getParent()
	{
		return 'datetime';
	}

	public function getName()
	{
		return 'datetime_picker';
	}
}
