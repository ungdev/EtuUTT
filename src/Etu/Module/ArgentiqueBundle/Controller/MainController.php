<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Doctrine\ORM\EntityManager;
use DPZ\Flickr;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/argentique")
 */
class MainController extends Controller
{
    /**
     * @Route("", name="argentique_index", options={"expose"=true})
     * @Template()
     */
    public function indexAction()
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        $sync = (bool) file_get_contents(
            $this->getKernel()->getBundle('EtuModuleArgentiqueBundle')->getPath() . '/Resources/config/synchronizing.bool'
        );

        var_dump($sync);
        exit;

        @$flickr = new Flickr(
            '03073c12e007751f01ee16ac5488c764',
            '838160e0782e8718',
            $this->generateUrl('argentique_index', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        $flickr->authenticate('read');

        $flickr = ArgentiqueAccount::createAccess($this->get('router'));

        $response = $flickr->call('flickr.collections.getTree', [
            'user_id' => '121768723@N02',
        ]);

        $collections = ArgentiqueFlickr::mapCollections($response['collections']['collection']);

        return [
            'collections' => $collections,
            'is_admin' => in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'))
        ];
    }

    /**
     * @Route("/gallery/{id}/{slug}", name="argentique_gallery")
     * @Template()
     */
    public function galleryAction($id)
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var NestedTreeRepository $repo */
        $repo = $em->getRepository('EtuModuleArgentiqueBundle:Gallery');

        $query = $repo->createQueryBuilder('g')
            ->select('g')
            ->orderBy('g.treeRoot, g.treeLeft', 'ASC')
            ->getQuery();

        $tree = $repo->buildTreeArray($query->getArrayResult());

        $isAdmin = in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'));

        if ($isAdmin && $this->get('session')->has('argentique_upload')) {
            /** @var Photo $photo */
            foreach ($this->get('session')->get('argentique_upload') as $photo) {
                @unlink($this->get('kernel')->getRootDir().'/../web/temp/'.$photo->getFile().'.jpg');
                @unlink($this->get('kernel')->getRootDir().'/../web/temp/'.$photo->getFile().'_thumb.jpg');
            }

            $this->get('session')->set('argentique_upload', []);
        }

        return [
            'galleries' => $tree,
            'gallery' => $gallery,
            'is_admin' => $isAdmin
        ];
    }
}
