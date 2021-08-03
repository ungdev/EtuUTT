<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\Organization;

class OrgaTransformer extends AbstractTransformer
{
    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @var OrgaMemberTransformer
     */
    protected $orgaMemberTransformer;

    public function __construct(UserTransformer $userTransformer, OrgaMemberTransformer $orgaMemberTransformer)
    {
        $this->userTransformer = $userTransformer;
        $this->orgaMemberTransformer = $orgaMemberTransformer;
    }

    /**
     * @param $orga
     *
     * @return array
     */
    public function transformUnique($orga, EmbedBag $includes)
    {
        return array_merge($this->getData($orga), $this->getIncludes($orga, $includes), $this->getLinks($orga));
    }

    /**
     * @return array
     */
    private function getData(Organization $orga)
    {
        return [
            'login' => $orga->getLogin(),
            'name' => $orga->getName(),
            'mail' => $orga->getContactMail(),
            'phone' => $orga->getContactPhone(),
            'presidentWanted' => $orga->getPresidentWanted(),
            'description' => $orga->getDescription(),
            'descriptionShort' => $orga->getDescriptionShort(),
            'website' => $orga->getWebsite(),
        ];
    }

    /**
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
                    'rel' => 'orga.president',
                    'uri' => ($orga->getPresident()) ? '/api/public/users/'.$orga->getPresident()->getLogin() : null,
                ],
                [
                    'rel' => 'orga.members',
                    'uri' => '/api/public/orgas/'.$orga->getLogin().'/members',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getIncludes(Organization $orga, EmbedBag $includes)
    {
        $embed = [
            'members' => [],
        ];

        if ($includes->has('members')) {
            foreach ($orga->getMemberships() as $membership) {
                $embed['members'][] = $this->orgaMemberTransformer->transform($membership, new EmbedBag(['user']));
            }
        } else {
            foreach ($orga->getMemberships() as $membership) {
                $embed['members'][] = $this->orgaMemberTransformer->transform($membership, new EmbedBag());
            }
        }

        return [
            '_embed' => $embed,
        ];
    }
}
