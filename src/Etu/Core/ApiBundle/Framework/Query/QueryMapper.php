<?php

namespace Etu\Core\ApiBundle\Framework\Query;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

interface QueryMapper
{
    /**
     * @return QueryBuilder
     */
    public function map(QueryBuilder $query, ParameterBag $request);
}
