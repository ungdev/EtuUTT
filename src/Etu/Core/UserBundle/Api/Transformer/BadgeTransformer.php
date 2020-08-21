<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\Badge;

class BadgeTransformer extends AbstractTransformer
{
    /**
     * @param Badge $badge
     *
     * @return array
     */
    public function transformUnique($badge, EmbedBag $includes)
    {
        return array_merge($this->getData($badge), $this->getLinks($badge));
    }

    /**
     * @return array
     */
    private function getData(Badge $badge)
    {
        return [
            'name' => $badge->getName(),
            'serie' => $badge->getSerie(),
            'level' => $badge->getLevel(),
        ];
    }

    /**
     * @return array
     */
    private function getLinks(Badge $badge)
    {
        return [
            '_links' => [
                [
                    'rel' => 'self',
                    'uri' => '/api/public/badges/'.$badge->getId(),
                ],
                [
                    'rel' => 'badge.image',
                    'uri' => '/img/badges/'.$badge->getPicture(),
                ],
            ],
        ];
    }
}
