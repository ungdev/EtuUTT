<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\ArgentiqueBundle\EtuModuleArgentiqueBundle;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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

    protected function createTree($directory)
    {
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
                    'pathname' => $file->getPathname(),
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
