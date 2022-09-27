<?php

namespace Etu\Module\ArgentiqueBundle\Command;

use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Module\ArgentiqueBundle\Controller\MainController;
use Etu\Module\ArgentiqueBundle\EtuModuleArgentiqueBundle;
use Etu\Module\ArgentiqueBundle\Glide\ImageBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

class WarmupCacheCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:argentique:warmup')
            ->setDescription('Warm-up the argentique galery cache.')
            ->addOption(
                'force-rebuild',
                'f',
                InputOption::VALUE_NONE,
                'Force rebuild of already created cache for all images'
            );
    }

    /**
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();

        $output->writeln('This command will generate the argentique galery cache. It will not clear it, to clear it you have to manualy remove the var/cache/argentique folder.');

        // Generate a list of all images
        $imageList = $this->listImages($root.'/');

        // We request each images in each modes to generate cache for all of them
        $progress = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($imageList));
        $i = 0;
        foreach ($imageList as $image) {
            $progress->update($i++);

            // Keep only relative path
            $image = mb_substr($image, mb_strlen($root));

            // Clear cache if rebuild is requested
            if ($input->getOption('force-rebuild')) {
                ImageBuilder::deleteCache($image);
            }

            // Generate each type of image
            ImageBuilder::createImageResponse($image, '');
            ImageBuilder::createImageResponse($image, 'slideshow');
            ImageBuilder::createImageResponse($image, 'thumbnail');
        }
        $progress->update(count($imageList));

        $output->writeln("\n");
        $output->writeln('Done.');
    }

    /**
     * Recursively list images files.
     *
     * @param mixed $directoryPath
     */
    protected function listImages($directoryPath)
    {
        $iterator = new \DirectoryIterator($directoryPath);
        $imageList = [];

        foreach ($iterator as $file) {
            if ('.' == mb_substr($file->getBasename(), 0, 1)) {
                continue;
            }

            if ($file->isDir()) {
                $imageList = array_merge($imageList, $this->listImages($file->getPathname()));
            } elseif (MainController::isAcceptableImage($file)) {
                $imageList[] = $file->getPathname();
            }
        }

        return $imageList;
    }
}
