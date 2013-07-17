<?php

namespace Etu\Core\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Guzzle\Http;

class AssetsCompileCommand extends ContainerAwareCommand
{
	/**
	 * @var string
	 */
	protected $modulesDirectory;

	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:assets:compile')
			->setDescription('Compile the assets for production')
		;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$directory = __DIR__.'/../../../../../web/';

		passthru(sprintf(
			'php %s/console fos:js-routing:dump',
			$directory.'../app'
		));

		$css = array(
			'bootstrap/css/bootstrap.min.css',
			'bootstrap/css/bootstrap-responsive.min.css',
			'redactor-js/redactor/redactor.css',
			'tipsy/src/tipsy.css',
			'facebox/src/facebox.css',
			'css/boot.css',
		);

		$js = array(
			'bootstrap/js/bootstrap.min.js',
			'redactor-js/redactor/redactor.min.js',
			'redactor-js/redactor/langs/fr.js',
			'facebox/src/facebox.js',
			'tipsy/src/jquery.tipsy.js',
			'bundles/fosjsrouting/js/router.js',
			'js/fos_js_routes.js',
			'js/common.js',
		);

		/*
		 * CSS
		 */
		$output->writeln("\nCompiling CSS...");
		$compiled = '';

		foreach ($css as $file) {
			$compiled .= ' '.file_get_contents($directory.$file);
		}

		// Send CSS to CSSMinifier
		$compiled = file_get_contents('http://cssminifier.com/raw', false, stream_context_create(array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query(array(
					'input' => $compiled
				))
			)
		)));

		file_put_contents($directory.'css/compiled.css', $compiled);

		$output->writeln("Compiled code written in web/css/compiled.css");


		/*
		 * Javascript
		 */
		$output->writeln("Compiling JS...");
		$compiled = '';

		foreach ($js as $file) {
			$compiled .= ' '.file_get_contents($directory.$file);
		}

		// Send JS to Google Closure that minify it in a really smart way
		$compiled = file_get_contents('http://closure-compiler.appspot.com/compile', false, stream_context_create(array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query(array(
					'js_code' => $compiled,
					'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
					'output_format' => 'text',
					'output_info' => 'compiled_code',
				))
			)
		)));

		file_put_contents($directory.'js/compiled.js', $compiled);

		$output->writeln("Compiled code written in web/js/compiled.js\n");

	}
}
