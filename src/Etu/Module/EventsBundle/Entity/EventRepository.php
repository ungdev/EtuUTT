<?php

namespace Etu\Module\EventsBundle\Entity;

use CalendR\Event\Provider\ProviderInterface;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository implements ProviderInterface
{
    /**
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param array     $options
     *
     * @return array
     */
    public function getEvents(\DateTime $begin, \DateTime $end, array $options = array())
    {
        $query = $this->createQueryBuilder('e')
            ->select('e, o')
            ->leftJoin('e.orga', 'o')
            ->where('e.begin >= :begin')
            ->setParameter('begin', $begin)
            ->andWhere('e.end < :end')
            ->setParameter('end', $end)
            ->orderBy('e.begin', 'ASC')
            ->addOrderBy('e.end', 'ASC');

        if (!isset($options['connected']) || !$options['connected']) {
            $query->andWhere('e.privacy <= :publicPrivacy')
                ->setParameter('publicPrivacy', Event::PRIVACY_PUBLIC);
        }

        return $query->getQuery()
            ->getResult();
    }
}
