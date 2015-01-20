<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\ArgentiqueBundle\EtuModuleArgentiqueBundle;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/argentique/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("", name="argentique_admin")
     * @Template()
     */
    public function indexAction()
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        if (! $this->getUser()->hasPermission('argentique.admin')) {
            throw new AccessDeniedHttpException();
        }

        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();

        $tree = $this->createTree($root);

        return [
            'tree' => $tree,
        ];
    }

    /**
     * @Route("/photos", name="argentique_admin_photos", options={"expose"=true})
     * @Template()
     */
    public function photosAction(Request $request)
    {
        if (! $this->getUserLayer()->isConnected() || ! $this->getUser()->hasPermission('argentique.admin')) {
            throw new AccessDeniedHttpException();
        }

        $path = urldecode($request->query->get('p'));

        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();

        /** @var \SplFileInfo[] $iterator */
        $iterator = new \DirectoryIterator($root . '/' . $path);

        /** @var array $photos */
        $photos = [];

        foreach ($iterator as $file) {
            if (substr($file->getBasename(), 0, 1) == '.') {
                continue;
            }

            if ($file->getExtension() == 'jpg' || $file->getExtension() == 'jpeg') {
                $size = getimagesize($file->getPathname());

                $photos[] = [
                    'extension' => $file->getExtension(),
                    'pathname' => str_replace($root . '/', '', $file->getPathname()),
                    'basename' => $file->getBasename(),
                    'filename' => $file->getBasename('.' . $file->getExtension()),
                    'size' => [
                        'width' => $size[0],
                        'height' => $size[1],
                        'ratio' => $size[1] / 150
                    ]
                ];
            }
        }

        return [
            'photos' => $photos,
        ];
    }

    /**
     * @Route("/upload", name="argentique_admin_upload")
     */
    public function uploadAction(Request $request)
    {
        if (! $this->getUserLayer()->isConnected() || ! $this->getUser()->hasPermission('argentique.admin')) {
            throw new AccessDeniedHttpException();
        }

        echo '<pre>';
        var_dump($request);
        echo '</pre>';
        exit;
    }


    /**
     * @param string $directory
     * @return array
     */
    protected function createTree($directory)
    {
        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();

        /** @var \SplFileInfo[] $iterator */
        $iterator = new \DirectoryIterator($directory);

        /** @var array $tree */
        $tree = [];

        foreach ($iterator as $file) {
            if ($file->isDir() && substr($file->getBasename(), 0, 1) != '.') {
                $score = $file->getBasename();

                if ($directory == EtuModuleArgentiqueBundle::getPhotosRoot()) {
                    $score = intval(substr($file->getBasename(), 1)) * 2;

                    if (substr($file->getBasename(), 0, 1) == 'A') {
                        $score += 1;
                    }
                }

                $tree[] = [
                    'id' => md5($file->getPathname()),
                    'text' => $file->getBasename(),
                    'children' => $this->createTree($file->getPathname()),
                    'data' => [
                        'basename' => $file->getBasename(),
                        'pathname' => str_replace($root . '/', '', $file->getPathname()),
                    ],
                    'score' => $score,
                ];
            }
        }

        usort($tree, function($a, $b) {
            return ($a['score'] > $b['score']) ? -1 : 1;
        });

        return $tree;
    }
}
