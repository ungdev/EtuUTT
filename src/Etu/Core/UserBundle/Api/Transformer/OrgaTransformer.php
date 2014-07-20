<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\Organization;

class OrgaTransformer extends AbstractTransformer
{
    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @param UserTransformer $userTransformer
     */
    public function __construct(UserTransformer $userTransformer)
    {
        $this->userTransformer = $userTransformer;
    }

    /**
     * @param Organization $orga
     * @return array
     */
    public function transformUnique($orga)
    {
        return [
            'login' => $orga->getLogin(),
            'name' => $orga->getName(),
            'mail' => $orga->getContactMail(),
            'phone' => $orga->getContactPhone(),
            'description' => $orga->getDescription(),
            'descriptionShort' => $orga->getDescriptionShort(),
            'website' => $orga->getWebsite(),
            'image' =>  '/uploads/logos/' . $orga->getAvatar(),
            'president' => ($orga->getPresident()) ? $this->userTransformer->transform($orga->getPresident()) : null,
        ];
    }
}