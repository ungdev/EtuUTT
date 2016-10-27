<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Etu\Core\CoreBundle\Framework\EtuKernel;
use Etu\Core\CoreBundle\Framework\Definition\Module;

/**
 * EtuUTT AppKernel. Redefine the way to load bundles for the modules system.
 */
class AppKernel extends EtuKernel
{
    /**
     * Register the bundles (and by the way the modules).
     *
     * @return array|\Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     *
     * @throws RuntimeException
     * @throws \ErrorException
     */
    public function registerBundles()
    {
        $bundles = [
            // Symfony
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            // Security management (authorization and authentication)
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            // Tempalting engine
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            // Logger library
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            // Mailing library
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            // Assets management
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
        //    new Minifier\MinifierBundle(),
            // Libraies
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // Doctrine
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            // Time library to display pretty dates
            new Knp\Bundle\TimeBundle\KnpTimeBundle(),
            // Useful paginator
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            // Generate routes from Javascript
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            // Calendar and events library
        //    vendor/yohang/calendr/src/CalendR/Calendar.php
            //Flnew FrequenceWeb\Bundle\CalendRBundle\FrequenceWebCalendRBundle(),
            // BBcode
            new FM\BbcodeBundle\FMBbcodeBundle(),
            // Internationalization
            new Sonata\IntlBundle\SonataIntlBundle(),
            // Api documentation
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),

            // EtuUTT
            new Etu\Core\CoreBundle\EtuCoreBundle(),
            new Etu\Core\UserBundle\EtuUserBundle(),
            new Etu\Core\ApiBundle\EtuCoreApiBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Elao\WebProfilerExtraBundle\WebProfilerExtraBundle();
        }

        /*
         * Modules bundles, loaded dynamically from app/config/modules.yml
         */
        $modules = Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->getRootDir().'/config/modules.yml'));

        if (array_key_exists('modules', $modules)) {
            if (!is_array($modules['modules'])) {
                throw new \ErrorException('Key "modules" in app/config/modules.yml must be an array');
            }

            foreach ($modules['modules'] as $module) {
                $bundleFile = 'src/'.str_replace('\\', '/', $module).'.php';

                if (!class_exists($module, false)) {
                    if (file_exists($this->getRootDir().'/../'.$bundleFile)) {
                        require $this->getRootDir().'/../'.$bundleFile;
                    } else {
                        throw new \RuntimeException(
                            sprintf(
                                'Module "%s" can not be loaded (file "%s" not found)', $module, $bundleFile
                            )
                        );
                    }
                }

                if (class_exists($module, false)) {
                    $module = new ReflectionClass($module);
                    $module = $module->newInstance();

                    if ($module instanceof Module) {
                        $bundles[] = $module;
                        $this->registerModuleDefinition($module);
                    } else {
                        throw new \ErrorException(
                            sprintf('Module "%s" must be an instance of Etu\Core\CoreBundle\Framework\Definition\Module.', get_class($module))
                        );
                    }
                } else {
                    throw new \RuntimeException(
                        sprintf('Module "%s" can not be loaded (class not found)', get_class($module))
                    );
                }
            }
        } else {
            throw new \RuntimeException('No modules defined ??');
        }

        $this->checkModulesIntegrity();

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        if (!empty($this->getEnvParameters()['kernel.cache_dir'])) {
            return $this->getEnvParameters()['kernel.cache_dir'].'/'.$this->environment;
        } else {
            return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
        }
    }

    public function getLogDir()
    {
        if (!empty($this->getEnvParameters()['kernel.logs_dir'])) {
            return $this->getEnvParameters()['kernel.logs_dir'];
        } else {
            return dirname(__DIR__).'/var/logs';
        }
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
