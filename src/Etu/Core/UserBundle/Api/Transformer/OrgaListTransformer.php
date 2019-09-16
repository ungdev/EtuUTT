<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\Organization;

class OrgaListTransformer extends AbstractTransformer
{
  
    /**
     * @param $orga
     * @param EmbedBag $includes
     *
     * @return array
     */
    public function transformUnique($orga, EmbedBag $includes)
    {
        return array_merge($this->getData($orga), $this->getLinks($orga));
    }

    /**
     * @param Organization $orga
     *
     * @return array
     */
    private function getData(Organization $orga)
    {
        return [
            'login' => $orga->getLogin(),
            'name' => $orga->getName(),
            'descriptionShort' => $orga->getDescriptionShort(),
        ];
    }

    /**
     * @param Organization $orga
     *
     * @return array
     */
    private function getLinks(Organization $orga)
    {
        return [
            '_links' => [
                [
                    'rel' => 'self',
                    'uri' => '/api/public/orgas/'.$orga->getLogin(),
                ],
                [
                    'rel' => 'orga.image',
                    'uri' => '/uploads/logos/'.$orga->getAvatar(),
                ],
                [
                    'rel' => 'orga.members',
                    'uri' => '/api/public/orgas/'.$orga->getLogin().'/members',
                ],
            ],
        ];
    }
}
