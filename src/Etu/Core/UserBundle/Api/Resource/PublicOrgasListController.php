<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\UserBundle\Entity\Organization;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class PublicOrgasListController extends ApiController
{
    /**
     * List of all the organizations in EtuUTT (display only public data).
     *
     * @ApiDoc(
     *   section = "Organization - Public data",
     *   description = "List of the orgas (scope: public)",
     *   filters={
     *      { "name"="name", "dataType"="string" }
     *   }
     * )
     *
     * @Route("/public/orgas", name="api_public_orgas_list", options={"expose"=true})
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $page = (int) $request->query->get('page', 1);

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('o, p')
            ->from('EtuUserBundle:Organization', 'o')
            ->leftJoin('o.president', 'p')
            ->orderBy('o.name');

        $query = $this->get('etu.api.orga.mapper')->map($query, $request->query);

        /** @var SlidingPagination $pagination */
        $pagination = $this->get('knp_paginator')->paginate($query, $page, 30);

        $previous = false;
        $next = false;

        if ($page > 1) {
            $previous = $this->generateUrl('api_public_orgas_list', ['page' => $page - 1]);
        }

        if ($page < $pagination->getPaginationData()['pageCount']) {
            $next = $this->generateUrl('api_public_orgas_list', ['page' => $page + 1]);
        }

        $embedBag = EmbedBag::createFromRequest($request);

        return $this->format([
            'pagination' => [
                'currentPage' => $pagination->getCurrentPageNumber(),
                'totalPages' => $pagination->getPaginationData()['pageCount'],
                'totalItems' => $pagination->getTotalItemCount(),
                'perPage' => $pagination->getItemNumberPerPage(),
                'previous' => $previous,
                'next' => $next,
            ],
            'embed' => $embedBag->getMap([ 'members' ]),
            'data' => $this->get('etu.api.orga.transformer')->transform($pagination->getItems(), $embedBag)
        ]);
    }

    /**
     * View a single organization informations, embeding members.
     *
     * @ApiDoc(
     *   section = "Organization - Public data",
     *   description = "View a single organisation (scope: public)",
     *   parameters={
     *      { "name"="login", "dataType"="string", "required"=true, "description"="Organization login" }
     *   }
     * )
     *
     * @Route("/public/orgas/{login}", name="api_public_orgas_view")
     * @Method("GET")
     */
    public function viewAction(Organization $orga)
    {
        return $this->format([
            'embed' => [ 'members' => true ],
            'data' => $this->get('etu.api.orga.transformer')->transform($orga, new EmbedBag([ 'members' ]))
        ]);
    }

    /**
     * View a given organization members and their associated informations.
     *
     * @ApiDoc(
     *   section = "Organization - Public data",
     *   description = "List of a given organization members (scope: public)",
     *   parameters={
     *      { "name"="login", "dataType"="string", "required"=true, "description"="Organization login" }
     *   }
     * )
     *
     * @Route("/public/orgas/{login}/members", name="api_public_orgas_members")
     * @Method("GET")
     */
    public function membersAction(Organization $orga)
    {
        return $this->format([
            'data' => $this->get('etu.api.orga_member.transformer')->transform($orga->getMemberships()->toArray(), new EmbedBag([ 'user' ]))
        ]);
    }

    /**
     * Deprecated, please don't use it anymore.
     *
     * List of all the organizations in EtuUTT (display only public data).
     *
     * @ApiDoc(
     *   section = "Organization - Public data",
     *   description = "Use /api/public/orgas instead. List of the orgas (scope: public)",
     *   deprecated = true,
     *   filters={
     *      { "name"="name", "dataType"="string" }
     *   }
     * )
     *
     * @Route("/orgas", name="api_orgas_list", options={"expose"=true})
     * @Method("GET")
     */
    public function listDeprecatedAction(Request $request)
    {
        return $this->listAction($request);
    }

    /**
     * Deprecated, please don't use it anymore.
     *
     * View a single organization informations, embeding members.
     *
     * @ApiDoc(
     *   section = "Organization - Public data",
     *   description = "Use /api/public/orgas/{login} instead. View a single organisation (scope: public)",
     *   deprecated = true,
     *   parameters={
     *      { "name"="login", "dataType"="string", "required"=true, "description"="User login" }
     *   }
     * )
     *
     * @Route("/orgas/{login}", name="api_orgas_view")
     * @Method("GET")
     */
    public function viewDeprecatedAction(Organization $orga)
    {
        return $this->viewAction($orga);
    }
}
