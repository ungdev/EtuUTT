<?php

namespace Etu\Api\Framework;

use Doctrine\DBAL\Connection;
use Etu\Api\Config\Collection;
use Etu\Api\DependencyInjection\Container;
use Etu\Api\Security\SecurityToken;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Resource
{
	/**
	 * @var \Etu\Api\DependencyInjection\Container
	 */
	protected $container;

	/**
	 * @param Container $container
	 */
	final public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param $service
	 * @return object
	 */
	public function get($service)
	{
		return $this->container->get($service);
	}

	/**
	 * @return Collection
	 */
	public function getConfig()
	{
		return $this->container->get('config');
	}

	/**
	 * @return Connection
	 */
	public function getDoctrine()
	{
		return $this->container->get('doctrine');
	}

	/**
	 * @return SecurityToken
	 */
	public function getSecurityToken()
	{
		return $this->container->get('security.token');
	}

	/**
	 * @return \Doctrine\Common\Cache\ApcCache
	 */
	public function getCache()
	{
		return new \Doctrine\Common\Cache\ApcCache();
	}

	/**
	 * @param $name
	 * @param array $parameters
	 * @param $referenceType
	 * @return mixed
	 */
	public function generateUrl($name, $parameters = array(), $referenceType = UrlGenerator::ABSOLUTE_PATH)
	{
		return $this->get('routing.generator')->generate($name, $parameters, $referenceType);
	}
}
