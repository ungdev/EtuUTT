<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Etu\Core\CoreBundle\Framework\Api\Definition\Resource;
use Etu\Core\CoreBundle\Framework\Api\Util\SortingExpression;

// Annotations
use Tga\Api\Framework\Annotations as Api;

/**
 * @Api\Resource(
 *      "/users/search/{term}/{sortExpression}/{page}",
 *      defaults={"sortExpression"="lastname:asc,firstname:asc", "page"=1}
 * )
 */
class UsersSearchResource extends Resource
{
	/**
	 * @Api\Operation(method="GET")
	 */
	public function getOperation($term, $sortExpression, $page)
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

		$term = '%'.trim(str_replace(' ', '%', $term), '%').'%';

		$count = $this->getDoctrine()->createQueryBuilder()
			->select('COUNT(*) AS count')
			->from('etu_users', 'u')
			->where('u.login LIKE :term')
			->orWhere('u.fullName LIKE :term')
			->setParameter('term', $term)
			->execute()
			->fetch();

		$results = $this->getDoctrine()->createQueryBuilder()
			->select('
					u.login, u.studentId, u.mail, u.firstName, u.lastName, u.fullName, u.formation,
					u.niveau AS level, u.branch, u.filiere AS speciality, u.isStudent, u.avatar AS picture,
					u.website, u.facebook, u.twitter, u.linkedin, u.viadeo
				')
			->from('etu_users', 'u')
			->where('u.login LIKE :term')
			->orWhere('u.fullName LIKE :term')
			->setParameter('term', $term)
			->setFirstResult(($page - 1) * 20)
			->setMaxResults(20)
			->execute()
			->fetchAll();

		$users = array();

		foreach ($results as $user) {
			$users[$user['login']] = $user;
			$users[$user['login']]['isStudent'] = (bool) $user['isStudent'];
			$users[$user['login']]['formation'] = ($user['formation'] == 'Nc') ? null : $user['formation'];

			$users[$user['login']]['resources'] = array(
				'view' => 'https://etu.utt.fr/api/users/galopint'
			);
		}

		$totalItems = (int) $count['count'];
		$totalPages = ceil($totalItems / 20);

		$previous = false;
		$next = false;

		if ($page > 1) {
			$previous = $this->generateUrl('users_search', array(
				'term' => $term,
				'sortExpression' => $sortExpression,
				'page' => $page - 1,
			));
		}

		if ($page < $totalPages) {
			$next = $this->generateUrl('users_search', array(
				'term' => $term,
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
