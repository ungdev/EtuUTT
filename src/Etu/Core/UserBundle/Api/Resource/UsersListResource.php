<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Etu\Core\CoreBundle\Framework\Api\Definition\Resource;
use Etu\Core\CoreBundle\Framework\Api\Model\User;
use Etu\Core\CoreBundle\Framework\Api\Util\SortingExpression;

// Annotations
use Tga\Api\Framework\Annotations as Api;

/**
 * @Api\Resource(
 *      "/users/{sortExpression}/{page}",
 *      requirements={"sortExpression"="([a-z]+:(asc|desc),?)+", "page"="\d+"},
 *      defaults={"sortExpression"="lastname:asc,firstname:asc", "page"=1}
 * )
 */
class UsersListResource extends Resource
{
	/**
	 * @Api\Operation(method="GET")
	 */
	public function getOperation($sortExpression, $page)
	{
		$this->getAuthorizationProxy()->needAppToken();
		$this->getAuthorizationProxy()->needUserToken();

		$orderBy = SortingExpression::getOrderBy($sortExpression, array(
			'firstname', 'lastname', 'fullname', 'level'
		));

		if ($orderBy === false) {
			return $this->getResponseBuilder()->createErrorResponse(500, 'Invalid sort expression');
		}

		if (empty($orderBy)) {
			$orderBy['lastname'] = 'ASC';
			$orderBy['firstname'] = 'ASC';
		}

		$count = $this->getDoctrine()->createQueryBuilder()
			->select('COUNT(*) AS count')
			->from('etu_users', 'u')
			->execute()
			->fetch();

		$results = $this->getDoctrine()->createQueryBuilder()
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
			->setFirstResult(($page - 1) * 20)
			->setMaxResults(20)
			->execute()
			->fetchAll();

		$users = array();

		foreach ($results as $user) {
			$user = new User($user);
			$users[$user->get('login')] = $user->toArray();
		}

		$totalItems = (int) $count['count'];
		$totalPages = ceil($totalItems / 20);

		$previous = false;
		$next = false;

		if ($page > 1) {
			$previous = $this->generateUrl('users_list', array(
				'sortExpression' => $sortExpression,
				'page' => $page - 1,
			));
		}

		if ($page < $totalPages) {
			$next = $this->generateUrl('users_list', array(
				'sortExpression' => $sortExpression,
				'page' => $page + 1,
			));
		}

		return array(
			'paging' => array(
				'current' => $page,
				'previous' => $previous,
				'next' => $next,
				'totalItems' => $totalItems,
				'totalPages' => $totalPages,
				'itemsPerPage' => 20,
			),
			'sort' => $orderBy,
			'users' => $users,
		);
	}
}
