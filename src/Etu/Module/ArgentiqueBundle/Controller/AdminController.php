<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\ArgentiqueBundle\Entity\Gallery;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/argentique/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/manage-galleries", name="argentique_admin_manage")
     * @Template()
     */
    public function manageAction()
    {
        $this->checkAccess();

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var NestedTreeRepository $repo */
        $repo = $em->getRepository('EtuModuleArgentiqueBundle:Gallery');

        $query = $repo->createQueryBuilder('g')
            ->select('g')
            ->orderBy('g.treeRoot, g.treeLeft', 'ASC')
            ->where('g.treeRoot = 1')
            ->getQuery();

        $tree = $repo->buildTreeArray($query->getArrayResult());

        var_dump($tree);
        exit;

        return [
            'galleries' => $tree
        ];
    }

    /**
     * @Route("/upload", name="argentique_admin_upload", options={"expose"=true})
     * @Template()
     */
    public function uploadAction()
    {
        $this->checkAccess();

        return new Response(json_encode(['status' => 'OK']));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function checkAccess()
    {
        if (! $this->getUser() ||
            ! in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'))) {
            throw new AccessDeniedHttpException('Only Argentique is authorized');
        }
    }
}
