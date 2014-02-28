<?php

namespace Etu\Core\ApiBundle\Controller\User;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\ApiBundle\Framework\Controller\ApiController;
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

        /** @var $users QueryBuilder */
        $query = $em->createQueryBuilder()
            ->select('u')
            ->from('EtuUserBundle:User', 'u')
            ->orderBy('u.lastName');

        if ($request->query->has('login')) {
            $query->andWhere('u.login = :login')
                ->setParameter('login', $request->query->get('login'));
        }

        if ($request->query->has('firstname')) {
            $query->andWhere('u.firstName LIKE :firstname')
                ->setParameter('firstname', '%'.$request->query->get('firstname').'%');
        }

        if ($request->query->has('lastname')) {
            $query->andWhere('u.lastName LIKE :lastname')
                ->setParameter('lastname', '%'.$request->query->get('lastname').'%');
        }

        if ($request->query->has('branch')) {
            $query->andWhere('u.branch = :branch')
                ->setParameter('branch', $request->query->get('branch'));
        }

        if ($request->query->has('level')) {
            $query->andWhere('u.niveau = :level')
                ->setParameter('level', $request->query->get('level'));
        }

        if ($request->query->has('speciality')) {
            $query->andWhere('u.filiere = :speciality')
                ->setParameter('speciality', $request->query->get('speciality'));
        }

        if ($request->query->has('isStudent')) {
            $query->andWhere('u.isStudent = :isStudent')
                ->setParameter('isStudent', (bool) $request->query->get('isStudent'));
        }

        /** @var SlidingPagination $pagination */
        $pagination = $this->get('knp_paginator')->paginate($query, $page, 50);

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
            'users' => $this->transform($pagination->getItems())
        ]);
    }

    /**
     * @param $input
     * @return array
     * @throws \InvalidArgumentException
     */
    private function transform($input)
    {
        $transformer = new UserTransformer();
        return $transformer->transform($input);
    }
}
