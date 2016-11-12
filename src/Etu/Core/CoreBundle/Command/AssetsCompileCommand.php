<?php

namespace Etu\Core\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Guzzle\Http;

class AssetsCompileCommand extends ContainerAwareCommand
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
            ->setName('etu:assets:compile')
            ->setDescription('Compile the assets for production')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = __DIR__.'/../../../../../web/';

        passthru(sprintf(
            'php %s/console fos:js-routing:dump',
            $directory.'../app'
        ));

        $css = [
            'bootstrap/css/bootstrap.min.css',
            'bootstrap/css/bootstrap-responsive.min.css',
            'tipsy/src/tipsy.css',
            'facebox/src/facebox.css',
            'css/boot.css',
        ];

        $js = [
            'bootstrap/js/bootstrap.min.js',
            'bundles/fosjsrouting/js/router.js',
            'js/fos_js_routes.js',
            'sceditor/languages/fr.js',
            'facebox/src/facebox.js',
            'tipsy/src/jquery.tipsy.js',
            'js/common.js',
        ];

        /*
         * CSS
         */
        $output->writeln("\nCompiling CSS...");
        $code = '';

        foreach ($css as $file) {
            $code .= "\n".file_get_contents($directory.$file);
        }

        // Send CSS to CSSMinifier
        file_put_contents($directory.'css/compiled.css', $this->minifyCss($code));
        $output->writeln('Compiled code written in web/css/compiled.css');

        /*
         * Javascript
         */
        $output->writeln('Compiling JS...');
        $code = '';

        foreach ($js as $file) {
            $code .= "\n".$this->minifyJs(file_get_contents($directory.$file));
        }

        file_put_contents($directory.'js/compiled.js', $code);
        $output->writeln("Written in web/js/compiled.js\n");
    }

    /**
     * @param $input
     *
     * @return string
     */
    protected function minifyCss($input)
    {
        return file_get_contents('http://cssminifier.com/raw', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'input' => $input,
                ]),
            ],
        ]));
    }

    /**
     * @param $input
     *
     * @return string
     */
    protected function minifyJs($input)
    {
        return \JShrink\Minifier::minify($input, ['flaggedComments' => false]);
    }
}
