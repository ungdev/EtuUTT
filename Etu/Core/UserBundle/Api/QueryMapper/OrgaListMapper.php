<?php

namespace Etu\Core\UserBundle\Api\QueryMapper;

use Doctrine\ORM\QueryBuilder;
use Etu\Core\ApiBundle\Framework\Query\QueryMapper;
use Symfony\Component\HttpFoundation\ParameterBag;

class OrgaListMapper implements QueryMapper
{
    /**
     * @return QueryBuilder
     */
    public function map(QueryBuilder $query, ParameterBag $request)
    {
        if ($request->has('name')) {
            $query->andWhere('o.name LIKE :name')
                ->setParameter('name', '%'.$request->get('name').'%');
        }

        if ($request->has('login')) {
            $query->andWhere('o.login = :login')
                ->setParameter('login', $request->get('login'));
        }

        return $query;
    }
}
