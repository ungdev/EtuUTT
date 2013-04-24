<?php

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\EtuKernel;

use Symfony\Component\HttpKernel\Exception\FatalErrorException;
use Symfony\Component\Config\Loader\LoaderInterface;


/**
 * EtuUTT AppKernel. Redefine the way to load bundles for the modules system.
 */
class AppKernel extends EtuKernel
{
	/**
	 * Register the bundles (and by the way the modules).
	 *
	 * @return array|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
	 * @throws RuntimeException
	 * @throws Symfony\Component\HttpKernel\Exception\FatalErrorException
	 */
	public function registerBundles()
    {
	    /*
	     * Basic bundles, required to load the website
	     */
        $bundles = array(

	        // Symfony
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),

	        // Doctrine
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

	        // Sensio extra
	        new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

	        // JMS extra
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),

	        // Knp libraries
	        new Knp\Bundle\TimeBundle\KnpTimeBundle(),
	        new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),

	        // FOS libraries
	        new FOS\JsRoutingBundle\FOSJsRoutingBundle(),

	        // Tga
	        new Tga\AudienceBundle\TgaAudienceBundle(),

	        // EtuUTT
            new Etu\Core\CoreBundle\EtuCoreBundle(),
            new Etu\Core\UserBundle\EtuUserBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

	    /*
	     * Modules bundles, loaded dynamically from app/config/modules.yml
	     */
	    $modules = Symfony\Component\Yaml\Yaml::parse($this->getRootDir().'/config/modules.yml');

	    if (isset($modules['modules'])) {
		    if (! is_array($modules['modules'])) {
			    throw new FatalErrorException('Key "modules" in app/config/modules.yml must be an array');
		    }

		    foreach ($modules['modules'] as $module) {
			    $bundleFile = 'src/'.str_replace('\\', '/', $module).'.php';

			    if (file_exists($this->getRootDir().'/../'.$bundleFile)) {
				    require $this->getRootDir().'/../'.$bundleFile;
			    } else {
				    throw new \RuntimeException(sprintf(
					    'Module "%s" can not be loaded (file "%s" not found)', $module, $bundleFile
				    ));
			    }

			    if (class_exists($module, false)) {
				    $module = new ReflectionClass($module);
				    $module = $module->newInstance();

					if ($module instanceof Module) {
						$bundles[] = $module;
						$this->registerModuleDefinition($module);
					} else {
						throw new FatalErrorException(sprintf(
							'Module "%s" must be an instance of Etu\Core\CoreBundle\Framework\Definition\Module.',
							get_class($module)
						));
					}
			    } else {
				    throw new \RuntimeException(sprintf(
					    'Module "%s" can not be loaded (class not found)', $module
				    ));
			    }
		    }
	    }

		$this->checkModulesIntegrity();

        return $bundles;
    }

	/**
	 * Register the configuration.
	 *
	 * @param Symfony\Component\Config\Loader\LoaderInterface $loader
	 */
	public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
