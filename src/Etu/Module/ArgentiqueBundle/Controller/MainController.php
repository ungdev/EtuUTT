<?php

namespace Etu\Module\ArgentiqueBundle\Controller;

use Doctrine\ORM\EntityManager;
use DPZ\Flickr;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\ArgentiqueBundle\Entity\Collection;
use Etu\Module\ArgentiqueBundle\Entity\Photo;
use Etu\Module\ArgentiqueBundle\Entity\PhotoSet;
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

        if ($sync) {
            return $this->render('EtuModuleArgentiqueBundle:Main:synchronizing.html.twig');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Collection[] $collections */
        $collections = $em->createQueryBuilder()
            ->select('c, s')
            ->from('EtuModuleArgentiqueBundle:Collection', 'c')
            ->leftJoin('c.sets', 's')
            ->getQuery()
            ->getResult();

        /** @var Photo[] $photos */
        $photos = $em->createQueryBuilder()
            ->select('p')
            ->from('EtuModuleArgentiqueBundle:Photo', 'p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        return [
            'collections' => $collections,
            'photos' => $photos,
            'is_admin' => $this->isAdmin()
        ];
    }

    /**
     * @Route("/set/{id}/{slug}", requirements={"id"="\d+"}, name="argentique_set")
     * @Template()
     */
    public function setAction($id)
    {
        if (! $this->getUserLayer()->isConnected()) {
            return $this->createAccessDeniedResponse();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Collection[] $collections */
        $collections = $em->createQueryBuilder()
            ->select('c, s')
            ->from('EtuModuleArgentiqueBundle:Collection', 'c')
            ->leftJoin('c.sets', 's')
            ->getQuery()
            ->getResult();

        /** @var PhotoSet $set */
        $set = $em->createQueryBuilder()
            ->select('s, p')
            ->from('EtuModuleArgentiqueBundle:PhotoSet', 's')
            ->leftJoin('s.photos', 'p')
            ->addOrderBy('p.createdAt', 'DESC')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (! $set) {
            return $this->createNotFoundException();
        }

        return [
            'collections' => $collections,
            'set' => $set,
            'is_admin' => $this->isAdmin()
        ];
    }

    /**
     * @return bool
     */
    private function isAdmin()
    {
        return in_array($this->getUser()->getLogin(), $this->container->getParameter('etu.argentique.authorized_admin'));
    }
}
