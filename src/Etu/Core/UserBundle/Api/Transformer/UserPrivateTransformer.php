<?php

namespace Etu\Core\UserBundle\Api\Transformer;

use Etu\Core\ApiBundle\Framework\Transformer\AbstractTransformer;
use Etu\Core\UserBundle\Entity\User;

class UserPrivateTransformer extends AbstractTransformer
{
    /**
     * @param User $user
     * @return array
     */
    public function transformUnique($user)
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
            'image' => [
                'official' => '/uploads/photos/'.$user->getLogin().'_official.jpg',
                'custom' => '/uploads/photos/'.$user->getAvatar(),
            ],
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