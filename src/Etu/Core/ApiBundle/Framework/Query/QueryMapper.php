<?php

namespace Etu\Core\ApiBundle\Framework\Query;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

interface QueryMapper
{
    /**
     * @param QueryBuilder $query
     * @param ParameterBag $request
     *
     * @return QueryBuilder
     */
    public function map(QueryBuilder $query, ParameterBag $request);
}
