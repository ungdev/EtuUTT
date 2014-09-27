<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Embed\EmbedBag;
use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\User;

class UserPrivateTransformer extends AbstractTransformer
{
    /**
     * @var BadgeTransformer
     */
    protected $badgeTransformer;

    /**
     * @param BadgeTransformer $badgeTransformer
     */
    public function __construct(BadgeTransformer $badgeTransformer)
    {
        $this->badgeTransformer = $badgeTransformer;
    }

    /**
     * @param $user
     * @param EmbedBag $includes
     * @return array
     */
    public function transformUnique($user, EmbedBag $includes)
    {
        return array_merge($this->getData($user), $this->getIncludes($user, $includes), $this->getLinks($user));
    }

    /**
     * @param User $user
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
            'branch' => $user->getBranch(),
            'level' => $user->getNiveau(),
            'speciality' => $user->getFiliere(),
            'phone' => $user->getPhoneNumber(),
            'phonePrivacy' => $this->displayPrivacy($user->getPhoneNumberPrivacy()),
            'sex' => $user->getSex(),
            'sexPrivacy' => $this->displayPrivacy($user->getSexPrivacy()),
            'nationality' => $user->getNationality(),
            'nationalityPrivacy' => $this->displayPrivacy($user->getNationalityPrivacy()),
            'adress' => $user->getAdress(),
            'adressPrivacy' => $this->displayPrivacy($user->getAdressPrivacy()),
            'postalCode' => $user->getPostalCode(),
            'postalCodePrivacy' => $this->displayPrivacy($user->getPostalCodePrivacy()),
            'city' => $user->getCity(),
            'cityPrivacy' => $this->displayPrivacy($user->getCityPrivacy()),
            'country' => $user->getCountry(),
            'countryPrivacy' => $this->displayPrivacy($user->getCountryPrivacy()),
            'birthday' => $user->getBirthday(),
            'birthdayPrivacy' => $this->displayPrivacy($user->getBirthdayPrivacy()),
            'birthdayDisplayOnlyAge' => $user->getBirthdayDisplayOnlyAge(),
            'personnalMail' => $user->getPersonnalMail(),
            'personnalMailPrivacy' => $this->displayPrivacy($user->getPersonnalMailPrivacy()),
            'uvs' => $user->getUvsList(),
            'surname' => $user->getSurnom(),
            'website' => $user->getWebsite(),
            'facebook' => $user->getFacebook(),
            'twitter' => $user->getTwitter(),
            'linkedin' => $user->getLinkedin(),
            'viadeo' => $user->getViadeo(),
            'isStudent' => $user->getIsStudent(),
            'bdeMember' => $user->hasActiveMembership(),
        ];
    }

    /**
     * @param User $user
     * @return array
     */
    private function getLinks(User $user)
    {
        return [
            '_links' => [
                [
                    'rel' => 'self',
                    'uri' => '/api/public/users/' . $user->getLogin()
                ],
                [
                    'rel' => 'user.badges',
                    'uri' => '/api/public/users/' . $user->getLogin() . '/badges'
                ],
                [
                    'rel' => 'user.image',
                    'uri' => '/uploads/photos/' . $user->getAvatar()
                ],
                [
                    'rel' => 'user.official_image',
                    'uri' => '/uploads/photos/'.$user->getLogin().'_official.jpg'
                ]
            ]
        ];
    }

    /**
     * @param User $user
     * @param EmbedBag $includes
     * @return array
     */
    private function getIncludes(User $user, EmbedBag $includes)
    {
        $embed = [
            'badges' => []
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
            '_embed' => $embed
        ];
    }

    protected function displayPrivacy($privacy)
    {
        if ($privacy == User::PRIVACY_PUBLIC) {
            return 'public';
        } else {
            return 'private';
        }
    }
}