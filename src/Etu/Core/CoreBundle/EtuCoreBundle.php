<?php

namespace Etu\Core\CoreBundle;

use Etu\Core\CoreBundle\Notification\Helper\HelperCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EtuCoreBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);

		$container->addCompilerPass(new HelperCompilerPass());
	}
}
