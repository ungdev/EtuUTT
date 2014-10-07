<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\Member;

class UserOrgasPrivateTransformer extends AbstractTransformer
{
    /**
     * @var OrgaTransformer
     */
    protected $orgaTransformer;

    /**
     * @param OrgaTransformer $orgaTransformer
     */
    public function __construct(OrgaTransformer $orgaTransformer)
    {
        $this->orgaTransformer = $orgaTransformer;
    }

    /**
     * @param $member
     * @param EmbedBag $includes
     * @return array|mixed
     */
    public function transformUnique($member, EmbedBag $includes)
    {
        return array_merge($this->getData($member), $this->getIncludes($member, $includes), $this->getLinks($member));
    }

    /**
     * @param Member $member
     * @return array
     */
    private function getData(Member $member)
    {
        return [
            'role' => Member::$roles[$member->getRole()],
            'permissions' => $member->getPermissions(),
        ];
    }

    /**
     * @param Member $member
     * @return array
     */
    private function getLinks(Member $member)
    {
        return [
            '_links' => [
                [
                    'rel' => 'member.organization',
                    'uri' => '/api/public/orgas/' . $member->getOrganization()->getLogin()
                ],
                [
                    'rel' => 'member.user',
                    'uri' => '/api/public/users/' . $member->getUser()->getLogin()
                ],
            ]
        ];
    }

    /**
     * @param Member $member
     * @param EmbedBag $includes
     * @return array
     */
    private function getIncludes(Member $member, EmbedBag $includes)
    {
        $embed = [
            'organization' => null,
        ];

        if ($includes->has('organization')) {
            $embed['organization'] = $this->orgaTransformer->transform($member->getOrganization());
        } else {
            $embed['organization'] = $member->getOrganization()->getLogin();
        }

        return [
            '_embed' => $embed
        ];
    }
}