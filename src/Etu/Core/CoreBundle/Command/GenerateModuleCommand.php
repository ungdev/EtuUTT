<?php

namespace Etu\Core\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class GenerateModuleCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected $modulesDirectory;

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:generate:module')
            ->setDescription('Generate an EtuUTT module.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->modulesDirectory = __DIR__.'/../../../Module/';

        $helper = $this->getHelper('question');

        $output->writeln('

  Welcome to the EtuUTT modules generator

This command helps you generate modules easily.
Each modules is hosted under the Etu\Module namespace.


What is your module name?
Its name is the bundle name without the "Bundle" prefix.
For instance, the UVBundle module name is "UV".
');

        $name = $helper->ask(
            $input, $output,
            new Question('Module name: ')
        );

        if (file_exists($this->modulesDirectory.'/'.$name.'Bundle')) {
            throw new \RuntimeException(sprintf(
                'A module called %s already exists in Etu\\Module namespace', $name
            ));
        }

        $output->writeln("\n\n".
                'What is your module identifier?'."\n".
                'Its identifier is a unique string that will be used'."\n".
                'by other modules that requires it.'."\n".
                'It should be a lowercase string.'."\n".
                'For instance, the UVBundle module identifier is "uv".'."\n"
        );

        $identifier = $helper->ask(
            $input, $output,
            new Question('Module identifier: ')
        );

        $this->createTree($name);
        $this->createFiles($name, $identifier);

        $output->writeln("\n\n".
                'The module has been generated. If you want to enable it, edit'."\n".
                '`app/AppKernel.php` and `app/config/modules.yml`.'
        );
    }

    /**
     * @param string $name
     */
    private function createTree($name)
    {
        mkdir($this->modulesDirectory.'/'.$name.'Bundle/Controller', 0777, true);
        mkdir($this->modulesDirectory.'/'.$name.'Bundle/DependencyInjection', 0777, true);
        mkdir($this->modulesDirectory.'/'.$name.'Bundle/Resources/config', 0777, true);
        mkdir($this->modulesDirectory.'/'.$name.'Bundle/Resources/views/Default', 0777, true);
        mkdir($this->modulesDirectory.'/'.$name.'Bundle/Resources/translations', 0777, true);
    }

    /**
     * @param string $name
     * @param string $identifier
     */
    private function createFiles($name, $identifier)
    {
        // Base module class
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/EtuModule'.$name.'Bundle.php',
            $this->replaceVariables($name, $identifier, file_get_contents(
                __DIR__.'/Template/BaseModuleBundle.tpl'
            ))
        );

        // Default controller
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/Controller/DefaultController.php',
            $this->replaceVariables($name, $identifier, file_get_contents(
                __DIR__.'/Template/Controller/DefaultController.tpl'
            ))
        );

        // DependencyInjection
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/DependencyInjection/Configuration.php',
            $this->replaceVariables($name, $identifier, file_get_contents(
                __DIR__.'/Template/DependencyInjection/Configuration.tpl'
            ))
        );
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/DependencyInjection/EtuModule'.$name.'Extension.php',
            $this->replaceVariables($name, $identifier, file_get_contents(
                __DIR__.'/Template/DependencyInjection/ModuleExtension.tpl'
            ))
        );

        // Resources
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/Resources/config/services.yml',
            file_get_contents(__DIR__.'/Template/Resources/config/services.yml')
        );
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/Resources/translations/messages.cn.yml',
            file_get_contents(__DIR__.'/Template/Resources/translations/messages.cn.yml')
        );
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/Resources/translations/messages.en.yml',
            file_get_contents(__DIR__.'/Template/Resources/translations/messages.en.yml')
        );
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/Resources/translations/messages.es.yml',
            file_get_contents(__DIR__.'/Template/Resources/translations/messages.es.yml')
        );
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/Resources/translations/messages.fr.yml',
            file_get_contents(__DIR__.'/Template/Resources/translations/messages.fr.yml')
        );
        file_put_contents(
            $this->modulesDirectory.'/'.$name.'Bundle/Resources/views/Default/index.html.twig',
            file_get_contents(__DIR__.'/Template/Resources/views/Default/index.html.twig')
        );
    }

    /**
     * @param string $name
     * @param string $identifier
     * @param string $string
     *
     * @return string
     */
    private function replaceVariables($name, $identifier, $string)
    {
        return str_replace(
            ['%name%', '%identifier%'],
            [$name, $identifier],
            $string
        );
    }
}
