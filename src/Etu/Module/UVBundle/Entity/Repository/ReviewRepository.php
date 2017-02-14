<?php

namespace Etu\Module\UVBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Etu\Module\UVBundle\Entity\UV;

class ReviewRepository extends EntityRepository
{
    /**
     * @param UV|UV[]|string[] $uv
     *
     * @throws \InvalidArgumentException
     *
     * @return QueryBuilder
     */
    public function createQbReviewOf(array $uv)
    {
        $codes = [];

        foreach ($uv as $item) {
            if (is_string($item)) {
                $codes[] = $item;
            } elseif ($item instanceof UV) {
                $codes[] = $item->getCode();
            } else {
                throw new \InvalidArgumentException();
            }
        }

        $qb = $this->createQueryBuilder('r');

        return $qb
            ->select('r, u')
            ->leftJoin('r.uv', 'u')
            ->where($qb->expr()->in('u.code', $codes));
    }
}
