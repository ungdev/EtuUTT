<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\Member;

class OrgaMemberTransformer extends AbstractTransformer
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
            'user' => null,
        ];

        if ($includes->has('user')) {
            $embed['user'] = $this->userTransformer->transform($member->getUser());
        } else {
            $embed['user'] = $member->getUser()->getLogin();
        }

        return [
            '_embed' => $embed
        ];
    }
}