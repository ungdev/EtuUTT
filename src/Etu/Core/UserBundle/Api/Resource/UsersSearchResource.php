<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Etu\Core\CoreBundle\Framework\Api\Definition\Resource;

// Annotations
use Swagger\Annotations as SWG;
use Tga\Api\Framework\Annotations as Api;

/**
 * @SWG\Resource(resourcePath="users")
 *
 * @Api\Resource(
 *      "/users/search/{term}/{sortExpression}/{page}",
 *      defaults={"sortExpression"="lastname:asc,firstname:asc", "page"=1}
 * )
 */
class UsersSearchResource extends Resource
{
	/**
	 * @SWG\Api(
	 *      path="/users/search/{term}/{sortExpression}/{page}",
	 *      description="Operations about users",
	 *      @SWG\Operations(
	 *          @SWG\Operation(
	 *              httpMethod="GET",
	 *              summary="Search users",
	 *              notes="Return a paginated list of all the users using a sorting expression matching given contraints",
	 *              responseClass="User",
	 *              nickname="findUsers",
	 *
	 *              @SWG\Parameters(
	 *                  @SWG\Parameter(
	 *                      name="term",
	 *                      description="Term to search",
	 *                      paramType="path",
	 *                      required="true",
	 *                      allowMultiple="false",
	 *                      dataType="string"
	 *                  )
	 *              ),
	 *              @SWG\Parameters(
	 *                  @SWG\Parameter(
	 *                      name="sortExpression",
	 *                      description="Expression to sort users. See documentation about SortingExpressions.",
	 *                      paramType="path",
	 *                      required="false",
	 *                      allowMultiple="false",
	 *                      defaultValue="lastname:asc,firstname:asc",
	 *                      dataType="string"
	 *                  )
	 *              ),
	 *              @SWG\Parameters(
	 *                  @SWG\Parameter(
	 *                      name="page",
	 *                      description="Page number",
	 *                      paramType="path",
	 *                      required="false",
	 *                      allowMultiple="false",
	 *                      defaultValue="1",
	 *                      dataType="int"
	 *                  )
	 *              )
	 *          )
	 *      )
	 * )
	 *
	 * @Api\Operation(method="GET")
	 */
	public function getOperation($term, $sortExpression, $page)
	{
		$orderBy = Sorting\Expression::getOrderBy($sortExpression, array(
			'firstname', 'lastname', 'fullname', 'level'
		));

		if ($orderBy === false) {
			return Response::error(Response::NOT_ACCEPTABLE, 'Invalid sort expression');
		}

		if (empty($orderBy)) {
			$orderBy['lastname'] = 'ASC';
			$orderBy['firstname'] = 'ASC';
		}

		$term = '%'.trim(str_replace(' ', '%', $term), '%').'%';

		if ($this->getCache()->contains('etuutt_api_users_search_'.$term.'_'.$sortExpression.'_'.$page)) {
			$cache = $this->getCache()->fetch('etuutt_api_users_search_'.$term.'_'.$sortExpression.'_'.$page);
			$count = $cache['count'];
			$users = $cache['users'];
		} else {
			$count = $this->getDoctrine()->createQueryBuilder()
				->select('COUNT(*) AS count')
				->from('etu_users', 'u')
				->where('u.fullName LIKE :term')
				->setParameter('term', $term)
				->execute()
				->fetch();

			$results = $this->getDoctrine()->createQueryBuilder()
				->select('
					u.id, u.login, u.studentId, u.mail, u.firstName, u.lastName, u.fullName, u.formation,
					u.niveau AS level, u.branch, u.filiere AS speciality, u.isStudent, u.avatar AS picture,
					u.website, u.facebook, u.twitter, u.linkedin, u.viadeo
				')
				->from('etu_users', 'u')
				->where('u.fullName LIKE :term')
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
			}

			$this->getCache()->save('etuutt_api_users_search_'.$term.'_'.$sortExpression.'_'.$page, array(
				'count' => $count,
				'users' => $users,
			), 3600);
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
