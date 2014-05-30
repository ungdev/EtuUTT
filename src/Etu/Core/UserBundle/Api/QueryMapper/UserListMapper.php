<?php

namespace Etu\Core\UserBundle\Api\QueryMapper;

use Doctrine\ORM\QueryBuilder;
use Etu\Core\ApiBundle\Framework\Query\QueryMapper;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserListMapper implements QueryMapper
{
    /**
     * @param QueryBuilder $query
     * @param ParameterBag $request
     * @return QueryBuilder
     */
    public function map(QueryBuilder $query, ParameterBag $request)
    {
        if ($request->has('firstname')) {
            $query->andWhere('u.firstName LIKE :firstname')
                ->setParameter('firstname', '%'.$request->get('firstname').'%');
        }

        if ($request->has('login')) {
            $query->andWhere('u.login = :login')
                ->setParameter('login', $request->get('login'));
        }

        if ($request->has('lastname')) {
            $query->andWhere('u.lastName LIKE :lastname')
                ->setParameter('lastname', '%'.$request->get('lastname').'%');
        }

        if ($request->has('name')) {
            $term = str_replace(' ', '%', trim($request->get('name')));

            $query->andWhere('u.firstName LIKE :term OR u.lastName LIKE :term OR u.fullName LIKE :term')
                ->setParameter('term', '%'.$term.'%');
        }

        if ($request->has('branch')) {
            $query->andWhere('u.branch = :branch')
                ->setParameter('branch', $request->get('branch'));
        }

        if ($request->has('level')) {
            $query->andWhere('u.niveau = :level')
                ->setParameter('level', $request->get('level'));
        }

        if ($request->has('speciality')) {
            $query->andWhere('u.filiere = :speciality')
                ->setParameter('speciality', $request->get('speciality'));
        }

        if ($request->has('is_student')) {
            $query->andWhere('u.isStudent = :is_student')
                ->setParameter('is_student', (bool) $request->get('is_student'));
        }

        if ($request->has('bde_member') && $request->get('bde_member') == '1') {
            $query->andWhere('m.end > :now')
                ->setParameter('now', new \DateTime('now'));
        }

        return $query;
    }
}