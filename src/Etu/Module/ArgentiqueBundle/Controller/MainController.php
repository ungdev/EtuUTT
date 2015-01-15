<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Etu\Module\ArgentiqueBundle\EtuModuleArgentiqueBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/argentique")
 */
class MainController extends Controller
{
    /**
     * @Route("", name="argentique_index")
     * @Route("/collection/{directory}", requirements={"directory"=".+"}, name="argentique_directory")
     * @Template()
     */
    public function indexAction($directory = null)
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();

        if (strpos($directory, './') !== false || ! file_exists($root . '/' . $directory)) {
            return $this->redirect($this->generateUrl('argentique_index'));
        }

        $breadcrumb = [];
        $dirParts = explode('/', $directory);

        $breadcrumb[] = [
            'basename' => 'Argentique',
            'pathname' => null,
        ];

        $pathname = '';

        foreach ($dirParts as $part) {
            if (empty($part)) {
                continue;
            }

            if (empty($pathname)) {
                $pathname = $part;
            } else {
                $pathname .= '/' . $part;
            }

            $breadcrumb[] = [
                'basename' => $part,
                'pathname' => $pathname,
            ];
        }

        /** @var \SplFileInfo[] $iterator */
        $iterator = new \DirectoryIterator($root . '/' . $directory);

        /** @var array $directories */
        $directories = [];

        /** @var array $photos */
        $photos = [];

        foreach ($iterator as $file) {
            if (substr($file->getBasename(), 0, 1) == '.') {
                continue;
            }

            if ($file->isDir()) {
                $basename = str_replace($root . '/' . (($directory) ? $directory . '/' : ''), '', $file->getPathname());

                $score = intval(substr($basename, 1)) * 2;

                if (substr($basename, 0, 1) == 'A') {
                    $score += 1;
                }

                $directories[] = [
                    'pathname' => str_replace($root . '/', '', $file->getPathname()),
                    'basename' => $basename,
                    'score' => $score,
                ];
            } elseif ($file->getExtension() == 'jpg' || $file->getExtension() == 'jpeg') {
                $photos[] = [
                    'extension' => $file->getExtension(),
                    'pathname' => str_replace($root . '/', '', $file->getPathname()),
                    'basename' => $file->getBasename(),
                    'filename' => $file->getBasename('.' . $file->getExtension()),
                ];
            }
        }

        usort($directories, function($a, $b) {
            return ($a['score'] < $b['score']) ? -1 : 1;
        });

        return [
            'breadcrumb' => $breadcrumb,
            'directory' => $directory,
            'photos' => $photos,
            'directories' => $directories,
        ];
    }
}
