<?php

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\EtuKernel;

use Symfony\Component\Debug\Exception\FatalErrorException;
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
	 * @throws Symfony\Component\Debug\Exception\FatalErrorException
	 */
	public function registerBundles()
    {
	    /*
	     * Basic bundles, required to load the website
	     */
        $bundles = array(
	        // Symfony
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(), // Symfony
            new Symfony\Bundle\SecurityBundle\SecurityBundle(), // Security management (authorization and authentication)
            new Symfony\Bundle\TwigBundle\TwigBundle(), // Tempalting engine
            new Symfony\Bundle\MonologBundle\MonologBundle(), // Logger library
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(), // Mailing library
	        new Symfony\Bundle\AsseticBundle\AsseticBundle(), // Assets management

	        // Doctrine
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(), // Doctrine ORM
	        new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(), // Fixtures are data which are used during testing
	        new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(), // Doctrine extensions

	        // Sensio extra
	        // Add annotations for controllers (@Template, @Cache, ...) and some useful other features
	        new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

	        // Knp libraries
	        new Knp\Bundle\TimeBundle\KnpTimeBundle(), // Time library to display pretty dates
	        new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(), // Useful paginator

	        // FOS libraries
	        new FOS\JsRoutingBundle\FOSJsRoutingBundle(), // Generate routes from Javascript

	        // CalendR
	        new FrequenceWeb\Bundle\CalendRBundle\FrequenceWebCalendRBundle(), // Calendar and events library

	        // BBCode
	        new FM\BbcodeBundle\FMBbcodeBundle(),

            // Date and times translations
            new Sonata\IntlBundle\SonataIntlBundle(),

	        // API documentation
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),

	        // EtuUTT
            new Etu\Core\CoreBundle\EtuCoreBundle(),
            new Etu\Core\UserBundle\EtuUserBundle(),
            new Etu\Core\ApiBundle\EtuCoreApiBundle(),
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

			    if (! class_exists($module, false)) {
				    if (file_exists($this->getRootDir().'/../'.$bundleFile)) {
					    require $this->getRootDir().'/../'.$bundleFile;
				    } else {
					    throw new \RuntimeException(sprintf(
						    'Module "%s" can not be loaded (file "%s" not found)', $module, $bundleFile
					    ));
				    }
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
