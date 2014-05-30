<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\UserBundle\Entity\User;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/users")
 */
class UserReadController extends ApiController
{
    /**
     * @ApiDoc(
     *   resource = true,
     *   description = "List of the users.",
     *   filters={
     *      { "name"="firstname",       "dataType"="string"     },
     *      { "name"="lastname",        "dataType"="string"     },
     *      { "name"="branch",          "dataType"="string"     },
     *      { "name"="level",           "dataType"="string"     },
     *      { "name"="speciality",      "dataType"="string"     },
     *      { "name"="is_student",      "dataType"="boolean"    },
     *      { "name"="bde_member",      "dataType"="boolean"    }
     *   }
     * )
     *
     * @Route("", name="api_users_list", options={"expose"=true})
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $page = (int) $request->query->get('page', 1);

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('u, m')
            ->from('EtuUserBundle:User', 'u')
            ->leftJoin('u.bdeMemberships', 'm')
            ->orderBy('u.lastName');

        $query = $this->get('etu.api.user.mapper')->map($query, $request->query);

        /** @var SlidingPagination $pagination */
        $pagination = $this->get('knp_paginator')->paginate($query, $page, 30);

        $previous = false;
        $next = false;

        if ($page > 1) {
            $previous = $this->generateUrl('api_users_list', ['page' => $page - 1], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($page < $pagination->getPaginationData()['pageCount']) {
            $next = $this->generateUrl('api_users_list', ['page' => $page + 1], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $this->format([
            'pagination' => [
                'currentPage' => $pagination->getCurrentPageNumber(),
                'totalPages' => $pagination->getPaginationData()['pageCount'],
                'totalItems' => $pagination->getTotalItemCount(),
                'perPage' => $pagination->getItemNumberPerPage(),
                'previous' => $previous,
                'next' => $next,
            ],
            'data' => $this->get('etu.api.user.transformer')->transform($pagination->getItems())
        ]);
    }

    /**
     * @ApiDoc(
     *   description = "View a single user",
     *   parameters={
     *      { "name"="login", "dataType"="string", "required"=true, "description"="User login" }
     *   }
     * )
     *
     * @Route("/{login}", name="api_users_view")
     * @Method("GET")
     */
    public function viewAction(User $user)
    {
        return $this->format([
            'data' => $this->get('etu.api.user.transformer')->transform($user)
        ]);
    }
}
