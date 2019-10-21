<?php

namespace Etu\Module\UVBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Module\UVBundle\Entity\Comment;
use Etu\Module\UVBundle\Entity\Review;
use Etu\Module\UVBundle\Entity\UV;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class UEController extends ApiController
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
     *
     * @param mixed $slug
     */
    public function detailAction($slug, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('u.slug, u.diplomes, u.code, u.mineurs, u.antecedents, u.name, u.category, u.cm, u.td, u.tp, u.the, u.stage, u.projet, u.automne, u.printemps, u.credits, u.objectifs, u.programme, u.languages, u.isOld')
            ->from('EtuModuleUVBundle:UV', 'u')
            ->where('u.deletedAt IS NULL')
            ->andWhere('u.slug = :slug')
            ->setParameter('slug', $slug);

        /** @var UV[] $uv */
        $uv = $query->getQuery()->getResult();
        if (count($uv) <= 0) {
            return $this->format(['error' => 'Not found'], 404, [], $request);
        }

        return $this->format($uv[0], 200, [], $request); // get only first element (there's no other one)
    }

    /**
     * You can get all UE's comments with this endpoint, using the UE's slug.
     *
     * @ApiDoc(
     *   section = "UEs",
     *   description = "comments of an UE (scope: public and is student)"
     * )
     *
     * @Route("/ues/{slug}/comments", name="api_ue_comments", options={"expose"=true})
     * @Method("GET")
     *
     * @param mixed $slug
     */
    public function commentsAction($slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW');
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('uv.slug, u.fullName, c.body, c.createdAt')
            ->from('EtuModuleUVBundle:Comment', 'c')
            ->join('c.uv', 'uv')
            ->join('c.user', 'u')
            ->where('c.deletedAt IS NULL')
            ->andWhere('uv.slug = :slug')
            ->setParameter('slug', $slug)
            ->orderBy('c.createdAt', 'DESC');

        /** @var Comment[] $comments */
        $comments = $query->getQuery()->getResult();

        return $this->format(['comments' => $comments], 200, [], $request);
    }

    /**
     * You can get all UE's reviews with this endpoint, using the UE's slug.
     *
     * @ApiDoc(
     *   section = "UEs",
     *   description = "reviews of an UE (scope: public and is student)"
     * )
     *
     * @Route("/ues/{slug}/reviews", name="api_ue_reviews", options={"expose"=true})
     * @Method("GET")
     *
     * @param mixed $slug
     */
    public function reviewAction($slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW');
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $uv = $em->getRepository('EtuModuleUVBundle:UV')
        ->findOneBy(['slug' => $slug]);
        /** @var Review[] $reviews */
        $reviews = $em->createQueryBuilder()
            ->select('r, s')
            ->from('EtuModuleUVBundle:Review', 'r')
            ->leftJoin('r.sender', 's')
            ->where('r.uv = :uv')
            ->andWhere('r.deletedAt IS NULL')
            ->setParameter('uv', $uv->getId())
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->format(['reviews' => $this->get('etu.api.review.transformer')->transform($reviews)], 200, [], $request);
    }

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
     *          "format" = "cs/tm/ec/me/ct/st/master",
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
            ->select('u.slug, u.code, u.name, u.category, u.automne, u.printemps')
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
            $query->andWhere('u.automne = :automne')
                ->setParameter('automne', $automne);
        }
        if ($request->query->has('printemps')) {
            $printemps = $request->query->get('printemps');
            $query->andWhere('u.printemps = :printemps')
              ->setParameter('printemps', $printemps);
        }
        /** @var UV[] $uvs */
        $uvs = $query->getQuery()->getResult();

        return $this->format(['ues' => $uvs], 200, [], $request);
    }
}
