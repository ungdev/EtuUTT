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
     * @param string $kernelRootDir path to app directory
     */
    public function __construct(BadgeTransformer $badgeTransformer, $kernelRootDir)
    {
        $this->badgeTransformer = $badgeTransformer;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * @param $user
     *
     * @return array
     */
    public function transformUnique($user, EmbedBag $includes)
    {
        return array_merge($this->getData($user), $this->getIncludes($user, $includes), $this->getLinks($user));
    }

    /**
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
            'personalMail' => User::PRIVACY_PUBLIC == $user->getPersonnalMailPrivacy() ? $user->getPersonnalMail() : null,
            'phone' => User::PRIVACY_PUBLIC == $user->getPhoneNumberPrivacy() ? $user->getPhoneNumber() : null,
            'nationality' => User::PRIVACY_PUBLIC == $user->getNationalityPrivacy() ? $user->getNationality() : null,
            'address' => User::PRIVACY_PUBLIC == $user->getAddressPrivacy() ? $user->getAddress() : null,
            'country' => User::PRIVACY_PUBLIC == $user->getCountryPrivacy() ? $user->getCountry() : null,
            'postalCode' => User::PRIVACY_PUBLIC == $user->getPostalCodePrivacy() ? $user->getPostalCode() : null,
            'city' => User::PRIVACY_PUBLIC == $user->getCityPrivacy() ? $user->getCity() : null,
            'sex' => User::PRIVACY_PUBLIC == $user->getSexPrivacy() ? $user->getSex() : null,
            'formation' => $user->getFormation(),
            'branch' => $user->getBranch(),
            'level' => $user->getNiveau(),
            'speciality' => $user->getFiliere(),
            'surname' => $user->getSurnom(),
            'jadis' => $user->getJadis(),
            'passions' => $user->getPassions(),
            'birthday' => (User::PRIVACY_PUBLIC == $user->getBirthdayPrivacy() && $user->getBirthday()) ? $user->getBirthday()->format(\DateTime::ISO8601) : null,
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
                'uri' => '/api/public/users/image/'.$user->getAvatar(),
            ],
        ];

        // add official image only if it exists
        $officialImage = [
            'rel' => 'user.official_image',
            'uri' => '/api/public/users/image/'.$user->getLogin().'_official.jpg',
        ];

        if (file_exists($this->kernelRootDir.'/../web/uploads/photos/'.$user->getLogin().'_official.jpg')) {
            array_push($links, $officialImage);
        }

        return [
            '_links' => $links,
        ];
    }

    /**
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
