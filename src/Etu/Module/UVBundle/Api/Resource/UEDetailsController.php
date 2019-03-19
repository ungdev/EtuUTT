<?php

namespace Etu\Module\UVBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Etu\Module\UVBundle\Entity\UV;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class UEDetailsController extends ApiController
{
    /**
     * You can get all the informations of an UE with this endpoint, using the UE's slug.
     * 
     * @ApiDoc(
     *   section = "UEs",
     *   description = "Details of an UE (scope: public)"
     * )
     *
     * @Route("/ues/{slug}", name="api_ue_details", options={"expose"=true})
     * @Method("GET")
     * @param mixed $slug
     */
    public function listAction($slug, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('u.slug, u.code, u.name, u.category, u.cm, u.td, u.tp, u.the, u.automne, u.printemps, u.credits, u.objectifs, u.programme')
            ->from('EtuModuleUVBundle:UV', 'u')
            ->where('u.deletedAt IS NULL')
            ->andWhere('u.isOld = 0')
            ->andWhere('u.slug = :slug')
            ->setParameter('slug', $slug);
       
        /** @var UV[] $uvs */
        $uv = $query->getQuery()->getResult();
        if(sizeof($uv) <= 0) {
          return $this->format(['error' => 'Not found'], 404, [], $request);
        }

        return $this->format($uv[0], 200, [], $request);// get only first element (there's no other one)
    }
}
