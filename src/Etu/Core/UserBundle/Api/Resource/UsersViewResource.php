<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Etu\Core\CoreBundle\Framework\Api\Definition\Resource;
use Etu\Core\CoreBundle\Framework\Api\Model\User;

// Annotations
use Tga\Api\Framework\Annotations as Api;

/**
 * @Api\Resource("/users/{login}", requirements={"login" = "[a-z0-9_-]+"})
 */
class UserViewResource extends Resource
{
	/**
	 * @Api\Operation(method="GET")
	 */
	public function getOperation($login)
	{
		$this->getAuthorizationProxy()->needAppToken();
		$this->getAuthorizationProxy()->needUserToken();

		$token = $this->getAuthenticationProxy()->getUserToken();

		$user = $this->getDoctrine()->createQueryBuilder()
			->select('
				u.login, u.studentId, u.mail, u.fullName, u.firstName, u.lastName, u.formation,
				u.niveau AS level, u.branch, u.filiere AS speciality, u.phoneNumber, u.phoneNumberPrivacy,
				u.title, u.room, u.avatar AS picture, u.sex, u.sexPrivacy, u.nationality,
				u.nationalityPrivacy, u.adress, u.adressPrivacy, u.postalCode, u.postalCodePrivacy,
				u.city, u.cityPrivacy, u.country, u.countryPrivacy, u.birthday, u.birthdayPrivacy,
				u.birthdayDisplayOnlyAge, u.personnalMail, u.personnalMailPrivacy, u.language,
				u.isStudent, u.surnom, u.jadis, u.passions,u.website, u.facebook, u.twitter,
				u.linkedin, u.viadeo, u.keepActive AS isExternal, u.semestersHistory
			')
			->from('etu_users', 'u')
			->where('u.id = :id')
			->setParameter('id', $token->getUser()->getId())
			->setMaxResults(1)
			->execute()
			->fetch();

		$user = new User($user);

		return $user->toArray();
	}
}
