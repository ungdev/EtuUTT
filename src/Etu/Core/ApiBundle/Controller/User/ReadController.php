<?php

namespace Etu\Core\ApiBundle\Controller\User;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Etu\Core\ApiBundle\Query\UserListMapper;
use Etu\Core\ApiBundle\Transformer\UserTransformer;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/users")
 */
class ReadController extends ApiController
{
    /**
     * @Route("/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"})
     * @Template()
     */
    public function listAction(Request $request, $page)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var $query QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('u, m')
            ->from('EtuUserBundle:User', 'u')
            ->leftJoin('u.bdeMemberships', 'm')
            ->orderBy('u.lastName');

        $query = (new UserListMapper())->mapQuery($query, $request->query);

        /** @var SlidingPagination $pagination */
        $pagination = $this->get('knp_paginator')->paginate($query, $page, 30);

        $previous = false;
        $next = false;

        if ($page > 1) {
            $previous = $this->generateUrl('etu_core_api_user_read_list', ['page' => $page - 1], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($page < $pagination->getPaginationData()['pageCount']) {
            $next = $this->generateUrl('etu_core_api_user_read_list', ['page' => $page + 1], UrlGeneratorInterface::ABSOLUTE_URL);
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
            'users' => (new UserTransformer())->transform($pagination->getItems())
        ]);
    }
}
