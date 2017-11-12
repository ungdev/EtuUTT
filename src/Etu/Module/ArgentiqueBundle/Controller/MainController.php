<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\ArgentiqueBundle\EtuModuleArgentiqueBundle;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\ServerFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

// Import annotations

/**
 * @Route("/argentique")
 */
class MainController extends Controller
{
    /**
     * Checks wether a file's extension is acceptable.
     *
     * @param mixed $file File to check
     *
     * @return bool Test result
     */
    public function isAcceptableImage($file)
    {
        $acceptedPhotosExtensions = ['jpg', 'jpeg', 'png'];

        return in_array(mb_strtolower($file->getExtension()), $acceptedPhotosExtensions);
    }

    /**
     * @Route("/photo/{file}", requirements={"file"=".+"}, name="argentique_view")
     *
     * @param mixed $file
     */
    public function viewAction($file)
    {
        $this->denyAccessUnlessGranted('ROLE_ARGENTIQUE_READ');

        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();
        $cache_root = $this->container->getParameter('kernel.cache_dir').'/';

        if (!file_exists($root.'/'.$file)) {
            throw $this->createNotFoundException('Picture not found');
        }

        $glide = ServerFactory::create(
            [
                'source' => $root,
                'cache' => $cache_root,
                'response' => new SymfonyResponseFactory(),
            ]
        );

        return $glide->getImageResponse($file, $_GET);
    }

    /**
     * @Route("/directory/{file}", requirements={"file"=".+"}, name="argentique_collectionImage")
     *
     * @param mixed $file
     */
    public function collectionImageAction(Request $request, $file)
    {
        $this->denyAccessUnlessGranted('ROLE_ARGENTIQUE_READ');

        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();
        $cache_root = $this->container->getParameter('kernel.cache_dir').'/';
        $web_root = $this->get('kernel')->getRootDir().'/../web/';

        $imagine = new Imagine();
        $cacheFile = $cache_root.md5($request->getPathInfo()).'.png';

        if (file_exists($cacheFile)) {
            $imagine->open($cacheFile)->show('png');
            exit;
        }

        if (!is_dir($root.'/'.$file)) {
            throw $this->createNotFoundException('Directory not found');
        }

        /** @var \SplFileInfo[]|\RecursiveIteratorIterator $iterator */
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/'.$file));

        $photo = false;

        foreach ($iterator as $file) {
            if ($this->isAcceptableImage($file)) {
                $size = getimagesize($file->getPathname());

                // Landscape images only
                if ($size[0] > $size[1]) {
                    $photo = $file->getPathname();
                    break;
                }
            }
        }

        $image = $imagine->open($web_root.'/assets/img/dirmask.png');

        if ($photo) {
            $photo = $imagine->open($photo)->thumbnail(new Box(300, 200), ImageInterface::THUMBNAIL_OUTBOUND);
            $image->paste($photo, new Point(36, 48));
        }

        $image = $image->thumbnail(new Box(132, 88), ImageInterface::THUMBNAIL_OUTBOUND);

        if ($photo) {
            $image->save($cacheFile);
        }

        return $image->show('png');
    }

    /**
     * @Route("", name="argentique_index")
     * @Route("/collection/{directory}", requirements={"directory"=".+"}, name="argentique_directory")
     * @Template()
     *
     * @param null|mixed $directory
     */
    public function indexAction($directory = null)
    {
        $this->denyAccessUnlessGranted('ROLE_ARGENTIQUE_READ');

        /** @var string $root */
        $root = EtuModuleArgentiqueBundle::getPhotosRoot();

        if (!file_exists($root)) {
            mkdir($root, 0777, true);
        }

        $directory = rtrim($directory, '/');

        if (mb_strpos($directory, './') !== false) {
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
                $pathname .= '/'.$part;
            }

            $breadcrumb[] = [
                'basename' => $part,
                'pathname' => $pathname,
            ];
        }

        if (!is_dir($root.'/'.$directory)) {
            throw $this->createNotFoundException('Collection not found');
        }

        /** @var \SplFileInfo[] $iterator */
        $iterator = new \DirectoryIterator($root.'/'.$directory);

        /** @var array $directories */
        $directories = [];

        /** @var array $photos */
        $photos = [];

        foreach ($iterator as $file) {
            if (mb_substr($file->getBasename(), 0, 1) == '.') {
                continue;
            }

            if ($file->isDir()) {
                $basename = str_replace($root.'/'.(($directory) ? $directory.'/' : ''), '', $file->getPathname());

                $score = $basename;

                if (!$directory) {
                    $score = (int) (mb_substr($basename, 1)) * 2;

                    if (mb_substr($basename, 0, 1) == 'A') {
                        $score += 1;
                    }
                }

                $directories[] = [
                    'pathname' => str_replace($root.'/', '', $file->getPathname()),
                    'basename' => $basename,
                    'score' => $score,
                ];
            } elseif ($this->isAcceptableImage($file)) {
                $size = getimagesize($file->getPathname());

                $photos[] = [
                    'extension' => $file->getExtension(),
                    'pathname' => str_replace($root.'/', '', $file->getPathname()),
                    'basename' => $file->getBasename(),
                    'filename' => $file->getBasename('.'.$file->getExtension()),
                    'size' => [
                        'width' => $size[0],
                        'height' => $size[1],
                        'ratio' => $size[1] / 150,
                    ],
                ];
            }
        }

        usort(
            $directories, function ($a, $b) {
                return ($a['score'] > $b['score']) ? -1 : 1;
            }
        );

        usort(
            $photos, function ($a, $b) {
                return strcasecmp($a['basename'], $b['basename']);
            }
        );

        return [
            'breadcrumb' => $breadcrumb,
            'directory' => $directory,
            'photos' => $photos,
            'directories' => $directories,
        ];
    }
}
