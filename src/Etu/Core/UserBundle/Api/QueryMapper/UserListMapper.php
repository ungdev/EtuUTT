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
     *
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

        if ($request->has('student_id')) {
            $query->andWhere('u.studentId = :student_id')
                ->setParameter('student_id', $request->get('student_id'));
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

        if ($request->has('bde_member')) {
            if ((bool) $request->get('bde_member')) {
                $query->andWhere('u.bdeMembershipEnd > CURRENT_TIMESTAMP()')
                    ->andWhere('u.bdeMembershipEnd IS NOT NULL');
            } else {
                $query->andWhere('u.bdeMembershipEnd < CURRENT_TIMESTAMP() OR u.bdeMembershipEnd IS NULL');
            }
        }

        if ($request->has('multifield')) {
            $multifield = str_replace(' ', '%', trim($request->get('multifield')));
            $query->andWhere(
                'u.firstName LIKE :multifieldLike
                OR u.lastName LIKE :multifieldLike
                OR u.fullName LIKE :multifieldLike
                OR u.mail LIKE :multifieldLike
                OR u.surnom LIKE :multifieldLike
                OR u.login = :multifield
                OR u.personnalMail = :multifield
                OR (u.studentId = :multifield AND u.studentId != 0)')
                ->setParameter('multifield', $multifield)
                ->setParameter('multifieldLike', '%'.$multifield.'%');
        }

        return $query;
    }
}
