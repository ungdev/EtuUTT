<?php

namespace Etu\Core\CoreBundle\Framework\Api\Security;

use Etu\Core\CoreBundle\Framework\Api\Security\ApplicationToken\AnonymousApplicationToken;
use Etu\Core\CoreBundle\Framework\Api\Security\ApplicationToken\ApplicationTokenInterface;
use Etu\Core\CoreBundle\Framework\Api\Security\UserToken\AnonymousUserToken;
use Etu\Core\CoreBundle\Framework\Api\Security\UserToken\UserTokenInterface;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\ApiBundle\Entity\ApplicationToken;
use Etu\Module\ApiBundle\Entity\UserToken;

use Tga\Api\Component\HttpFoundation\Response;
use Tga\Api\Framework\HttpKernel\Event\KernelRequestEvent;
use Tga\Api\Component\Cache\CacheManipulator;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationProxy
{
	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $doctrine;

	/**
	 * @var ApplicationTokenInterface
	 */
	protected $applicationToken;

	/**
	 * @var UserTokenInterface
	 */
	protected $userToken;

	/**
	 * @param Connection $doctrine
	 * @param CacheManipulator $cache
	 */
	public function __construct(Connection $doctrine, CacheManipulator $cache)
	{
		$this->doctrine = $doctrine;
		$this->applicationToken = new AnonymousApplicationToken();
		$this->userToken = new AnonymousUserToken();
	}

	/**
	 * @param KernelRequestEvent $event
	 */
	public function onKernelRequest(KernelRequestEvent $event)
	{
		if ($applicationTokenString = $this->findApplicationTokenString($event->getRequest())) {
			$this->fetchApplicationToken($applicationTokenString);
		}

		if ($userTokenString = $this->findUserTokenString($event->getRequest())) {
			$this->fetchUserToken($userTokenString);
		}
	}

	/**
	 * @return \Etu\Core\CoreBundle\Framework\Api\Security\ApplicationToken\ApplicationTokenInterface
	 */
	public function getApplicationToken()
	{
		return $this->applicationToken;
	}

	/**
	 * @return \Etu\Core\CoreBundle\Framework\Api\Security\UserToken\UserTokenInterface
	 */
	public function getUserToken()
	{
		return $this->userToken;
	}

	/**
	 * @param Request $request
	 * @return bool|string
	 */
	private function findApplicationTokenString(Request $request)
	{
		if ($request->headers->has('Application-Token')) {
			return $request->headers->get('Application-Token');
		} elseif ($request->query->has('app_token')) {
			return $request->query->get('app_token');
		}

		return false;
	}

	/**
	 * @param Request $request
	 * @return bool|string
	 */
	private function findUserTokenString(Request $request)
	{
		if ($request->headers->has('User-Token')) {
			return $request->headers->get('User-Token');
		} elseif ($request->query->has('user_token')) {
			return $request->query->get('user_token');
		}

		return false;
	}

	/**
	 * @param $tokenString
	 */
	private function fetchApplicationToken($tokenString)
	{
		$token = false;
		$cacheDriver = new \Doctrine\Common\Cache\ApcCache();

		if (function_exists('apc_fetch') && $cacheDriver->contains('etu_api_tokens_applications_'.$tokenString)) {
			$token = $cacheDriver->fetch('etu_api_tokens_applications_'.$tokenString);
		}

		if (! $token) {
			$tokenData = $this->doctrine->createQueryBuilder()
				->select('t.*')
				->from('etu_api_tokens_applications', 't')
				->where('t.token = :token')
				->setParameter('token', $tokenString)
				->setMaxResults(1)
				->execute()
				->fetch(\PDO::FETCH_ASSOC);

			if ($tokenData) {
				$token = new ApplicationToken();
				$token->setName($tokenData['name']);
				$token->setToken($tokenData['token']);
				$token->setCreatedAt(\DateTime::createFromFormat('d-m-Y H:i:s', $tokenData['createdAt']));

				$reflection = new \ReflectionObject($token);
				$property = $reflection->getProperty('id');
				$property->setAccessible(true);
				$property->setValue($token, (int) $tokenData['id']);

				if (function_exists('apc_fetch')) {
					$cacheDriver->save('etu_api_tokens_applications_'.$tokenString, $token, 3600);
				}

				$this->applicationToken = $token;
			}
		}
	}

	/**
	 * @param $tokenString
	 */
	private function fetchUserToken($tokenString)
	{
		$token = false;
		$cacheDriver = new \Doctrine\Common\Cache\ApcCache();

		if (function_exists('apc_fetch') && $cacheDriver->contains('etu_api_tokens_users_'.$tokenString)) {
			$token = $cacheDriver->fetch('etu_api_tokens_users_'.$tokenString);
		}

		if (! $token) {
			$tokenData = $this->doctrine->createQueryBuilder()
				->select('
					t.*,
					u.id AS user_id,
					u.login AS user_login,
					u.studentId AS user_student_id,
					u.mail AS mail,
					u.fullName AS user_name
				')
				->from('etu_api_tokens_users', 't')
				->leftJoin('t', 'etu_users', 'u', 't.user_id = u.id')
				->where('t.token = :token')
				->setParameter('token', $tokenString)
				->setMaxResults(1)
				->execute()
				->fetch(\PDO::FETCH_ASSOC);

			if ($tokenData) {
				$user = new User();

				$reflection = new \ReflectionObject($user);
				$property = $reflection->getProperty('id');
				$property->setAccessible(true);
				$property->setValue($user, (int) $tokenData['user_id']);

				$user->setLogin($tokenData['user_login']);
				$user->setStudentId($tokenData['user_student_id']);
				$user->setMail($tokenData['user_mail']);
				$user->setFullName($tokenData['user_name']);

				$token = new UserToken();
				$token->setUser($user);
				$token->setApplication($this->applicationToken);
				$token->setToken($tokenData['token']);
				$token->setCreatedAt(\DateTime::createFromFormat('d-m-Y H:i:s', $tokenData['createdAt']));

				$reflection = new \ReflectionObject($token);
				$property = $reflection->getProperty('id');
				$property->setAccessible(true);
				$property->setValue($token, (int) $tokenData['id']);

				if (function_exists('apc_fetch')) {
					$cacheDriver->save('etu_api_tokens_users_'.$tokenString, $token, 3600);
				}

				$this->userToken = $token;
			}
		}
	}
}
