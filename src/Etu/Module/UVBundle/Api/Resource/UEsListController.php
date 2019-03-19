<?php

namespace Etu\Module\UVBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Module\UVBundle\Entity\UV;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class UEsListController extends ApiController
{
    /**
     * To access an UE's information, you need to know the slug of that UE. For exemple,
     * TN02's slug is "tn02".
     *
     * This endpoint gives you a list of ue's
     *
     * @ApiDoc(
     *   section = "UEs",
     *   description = "List of all ues (scope: public)",
     *   parameters = {
     *      {
     *          "name" = "category",
     *          "required" = false,
     *          "dataType" = "string",
     *          "format" = "cs/tm/ec/me/ct/st/other",
     *          "description" = "Filter by the chosen category"
     *      },
     *      {
     *          "name" = "automne",
     *          "required" = false,
     *          "dataType" = "number",
     *          "format" = "0/1",
     *          "description" = "Filter ues by automne/not automne"
     *      },
     *      {
     *          "name" = "printemps",
     *          "required" = false,
     *          "dataType" = "number",
     *          "format" = "0/1",
     *          "description" = "Filter ues by printemps/not printemps"
     *      }
     *   }
     * )
     *
     * @Route("/ues", name="api_ues_list", options={"expose"=true})
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('u.slug, u.code, u.name')
            ->from('EtuModuleUVBundle:UV', 'u')
            ->where('u.deletedAt IS NULL')
            ->andWhere('u.isOld = 0');
        if ($request->query->has('category')) {
            $category = $request->query->get('category');
            $query->andWhere('u.category = :category')
                ->setParameter('category', $category);
        }
        if ($request->query->has('automne')) {
            $automne = $request->query->get('automne');
            $logger = $this->get('monolog.logger.admin');
            $logger->info($automne);
            $query->andWhere('u.automne = :automne')
                ->setParameter('automne', $automne);
        }
        /** @var UV[] $uvs */
        $uvs = $query->getQuery()->getResult();

        return $this->format($uvs, 200, [], $request);
    }
}
