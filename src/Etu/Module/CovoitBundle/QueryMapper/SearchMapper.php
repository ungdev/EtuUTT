<?php

namespace Etu\Module\CovoitBundle\QueryMapper;

use Doctrine\ORM\QueryBuilder;
use Etu\Module\CovoitBundle\Model\Search;

class SearchMapper
{
    /**
     * @return QueryBuilder
     */
    public function map(QueryBuilder $qb, Search $search)
    {
        $qb->select('c, s')
            ->from('EtuModuleCovoitBundle:Covoit', 'c')
            ->leftJoin('c.subscriptions', 's');

        if (!$search->olds) {
            $qb->andWhere('c.date >= CURRENT_DATE()');
        }

        if ($search->startCity) {
            $qb->andWhere('c.startCity = :startCity')
                ->setParameter('startCity', $search->startCity->getId());
        }

        if ($search->endCity) {
            $qb->andWhere('c.endCity = :endCity')
                ->setParameter('endCity', $search->endCity->getId());
        }

        if ($search->date) {
            if ($search->dateBeforeAfter) {
                $before = clone $search->date;
                $before->add(\DateInterval::createFromDateString('-1 day'));

                $after = clone $search->date;
                $after->add(\DateInterval::createFromDateString('1 day'));

                $qb->andWhere('c.date BETWEEN :before AND :after')
                    ->setParameter('before', $before->format('Y-m-d').' 00:00:00')
                    ->setParameter('after', $after->format('Y-m-d').' 00:00:00');
            } else {
                $qb->andWhere('c.date = :date')
                    ->setParameter('date', $search->date->format('Y-m-d').' 00:00:00');
            }
        }

        if ($search->priceMax) {
            $qb->andWhere('c.price <= :priceMax')
                ->setParameter('priceMax', $search->priceMax);
        }

        if ($search->hourMin) {
            $qb->andWhere('c.startHour >= :hourMin')
                ->setParameter('hourMin', $search->hourMin->format('H:i:s'));
        }

        if ($search->hourMax) {
            $qb->andWhere('c.startHour <= :hourMax')
                ->setParameter('hourMax', $search->hourMax->format('H:i:s'));
        }

        if ($search->keywords) {
            $qb->andWhere('c.notes LIKE :keywords')
                ->setParameter('keywords', '%'.implode('%', explode(' ', $search->keywords)).'%');
        }

        if ($search->placesLeft) {
            $qb->having('(c.capacity - COUNT(s)) >= :placesLeft')
                ->setParameter('placesLeft', $search->placesLeft);
        }

        return $qb;
    }
}
