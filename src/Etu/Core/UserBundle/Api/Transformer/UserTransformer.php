<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\User;

class UserTransformer extends AbstractTransformer
{
    /**
     * @var BadgeTransformer
     */
    protected $badgeTransformer;

    /**
     * @var KernelRootDir
     */
    protected $kernelRootDir;

    /**
     * @param BadgeTransformer $badgeTransformer
     * @param string           $kernelRootDir    path to app directory
     */
    public function __construct(BadgeTransformer $badgeTransformer, $kernelRootDir)
    {
        $this->badgeTransformer = $badgeTransformer;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * @param $user
     * @param EmbedBag $includes
     *
     * @return array
     */
    public function transformUnique($user, EmbedBag $includes)
    {
        return array_merge($this->getData($user), $this->getIncludes($user, $includes), $this->getLinks($user));
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function getData(User $user)
    {
        return [
            'login' => $user->getLogin(),
            'studentId' => $user->getStudentId(),
            'email' => $user->getMail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'fullName' => $user->getFullName(),
            'personalMail' => $user->getPersonnalMailPrivacy() == User::PRIVACY_PUBLIC ? $user->getPersonnalMail() : null,
            'phone' => $user->getPhoneNumberPrivacy() == User::PRIVACY_PUBLIC ? $user->getPhoneNumber() : null,
            'nationality' => $user->getNationalityPrivacy() == User::PRIVACY_PUBLIC ? $user->getNationality() : null,
            'address' => $user->getAddressPrivacy() == User::PRIVACY_PUBLIC ? $user->getAddress() : null,
            'country' => $user->getCountryPrivacy() == User::PRIVACY_PUBLIC ? $user->getCountry() : null,
            'postalCode' => $user->getPostalCodePrivacy() == User::PRIVACY_PUBLIC ? $user->getPostalCode() : null,
            'city' => $user->getCityPrivacy() == User::PRIVACY_PUBLIC ? $user->getCity() : null,
            'sex' => $user->getSexPrivacy() == User::PRIVACY_PUBLIC ? $user->getSex() : null,
            'formation' => $user->getFormation(),
            'branch' => $user->getBranch(),
            'level' => $user->getNiveau(),
            'speciality' => $user->getFiliere(),
            'surname' => $user->getSurnom(),
            'jadis' => $user->getJadis(),
            'passions' => $user->getPassions(),
            'birthday' => ($user->getBirthdayPrivacy() == User::PRIVACY_PUBLIC && $user->getBirthday()) ? $user->getBirthday()->format(\DateTime::ISO8601) : null,
            'website' => $user->getWebsite(),
            'facebook' => $user->getFacebook(),
            'uvs' => $user->getUvsList(),
            'twitter' => $user->getTwitter(),
            'linkedin' => $user->getLinkedin(),
            'viadeo' => $user->getViadeo(),
            'isStudent' => $user->getIsStudent(),
            'bdeMember' => $user->isBdeMember(),
            'bdeMembershipEnd' => $user->getBdeMembershipEnd(),
        ];
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function getLinks(User $user)
    {
        $links = [
            [
                'rel' => 'self',
                'uri' => '/api/public/users/'.$user->getLogin(),
            ],
            [
                'rel' => 'user.badges',
                'uri' => '/api/public/users/'.$user->getLogin().'/badges',
            ],
            [
                'rel' => 'user.image',
                'uri' => '/uploads/photos/'.$user->getAvatar(),
            ],
        ];

        // add official image only if it exists
        $officialImage = [
            'rel' => 'user.official_image',
            'uri' => '/uploads/photos/'.$user->getLogin().'_official.jpg',
        ];

        if (file_exists($this->kernelRootDir.'/../web'.$officialImage['uri'])) {
            array_push($links, $officialImage);
        }

        return [
            '_links' => $links,
        ];
    }

    /**
     * @param User     $user
     * @param EmbedBag $includes
     *
     * @return array
     */
    private function getIncludes(User $user, EmbedBag $includes)
    {
        $embed = [
            'badges' => [],
        ];

        if ($includes->has('badges')) {
            foreach ($user->getBadges() as $userBadge) {
                $embed['badges'][] = $this->badgeTransformer->transform($userBadge->getBadge(), new EmbedBag());
            }
        } else {
            foreach ($user->getBadges() as $userBadge) {
                $embed['badges'][] = $userBadge->getBadge()->getId();
            }
        }

        return [
            '_embed' => $embed,
        ];
    }
}
