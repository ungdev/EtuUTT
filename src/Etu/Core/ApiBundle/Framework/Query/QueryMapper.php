<?php

namespace Etu\Core\ApiBundle\Framework\Query;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class QueryMapper
{
    /**
     * @param QueryBuilder $query
     * @param ParameterBag $request
     * @return QueryBuilder
     */
    abstract public function mapQuery(QueryBuilder $query, ParameterBag $request);
}