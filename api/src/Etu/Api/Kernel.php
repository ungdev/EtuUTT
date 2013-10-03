<?php

namespace Etu\Api;

use Etu\Api\Http\Dumper\JsonDumper;
use Etu\Api\Http\Dumper\XmlDumper;
use Etu\Api\Http\Response;
use Etu\Api\Security\SecurityToken;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Kernel
{
	/**
	 * @var bool
	 */
	protected $debug = false;

	/**
	 * @var DependencyInjection\Container
	 */
	protected $container;

	/**
	 * Constructor
	 */
	public function __construct($debug = false)
	{
		$this->debug = $debug;

		$handler = new Debug\ExceptionHandler($this->debug);
		set_exception_handler(array($handler, 'handle'));
	}

	/**
	 * Boot the API kernel
	 */
	public function boot()
	{
		$this->container = new DependencyInjection\Container();

		$this->loadConfig();
		$this->loadServices();
		$this->registerExtensions();
		$this->loadExtensions();

		return $this;
	}

	/**
	 * Handle the request
	 */
	public function handle(Request $request)
	{
		Response::registerDumper(new JsonDumper());
		Response::registerDumper(new XmlDumper());

		$query = $request->query;

		if ($query->has('format')) {
			if ($query->get('format') == 'xml') {
				Response::setContentType(Response::CONTENT_TYPE_XML);
			} else {
				Response::setContentType(Response::CONTENT_TYPE_JSON);
			}
		} else {
			Response::useRequestAcceptedContentType($request);
		}

		if (! $this->isValidSecurityToken($request)) {
			return Response::error(403, 'Invalid API token');
		}

		$context = new RequestContext();
		$context->fromRequest($request);

		$routes = $this->container->get('routing.routes');
		$routingMatcher = new UrlMatcher($routes, $context);

		$this->container->set('routing.matcher', $routingMatcher);
		$this->container->set('routing.generator', new UrlGenerator($routes, $context));

		$requestUri = parse_url($request->getRequestUri(), PHP_URL_PATH);

		if (empty($requestUri)) {
			$requestUri = '/';
		}

		/** @var array $routeParam */
		$routeParam = $routingMatcher->match($requestUri);

		/** @var \ReflectionClass $reflection */
		$reflection = $routeParam['resource'];

		$methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

		/** @var CachedReader $reader */
		$reader = $this->container->get('annotations.reader');

		$action = false;
		$allowedMethods = array();

		foreach ($methods as $method) {
			$annotation = $reader->getMethodAnnotation($method, 'Etu\\Api\\Annotations\\Operation');

			if ($annotation instanceof Annotations\Operation) {
				$allowedMethods[] = $annotation->getMethod();

				if ($annotation->getMethod() == $request->getMethod()) {
					$action = $method;
				}
			}
		}

		if (! $action) {
			throw new MethodNotAllowedException($allowedMethods);
		}

		$resource = $reflection->newInstance($this->container);

		$arguments = $action->getParameters();
		$actionParameters = array();

		/** @var \ReflectionParameter $argument */
		foreach ($arguments as $argument) {
			if ($argument->getClass() && $argument->getClass()->getName() == 'Symfony\Component\HttpFoundation\Request') {
				$actionParameters[] = $request;
			} else {
				if (! isset($routeParam[$argument->getName()])) {
					throw new \RuntimeException(sprintf('Argument %s don\'t provided.', $argument->getName()));
				}

				$actionParameters[] = $routeParam[$argument->getName()];
			}
		}

		$response = $action->invokeArgs($resource, $actionParameters);

		if (is_array($response)) {
			$response = Response::success($response);
		}

		if (! $response instanceof Response) {
			throw new \RuntimeException(sprintf(
				'A resource operation must return an array or a Response object (%s given)',
				gettype($response)
			));
		}

		return $response;
	}

	/**
	 * Load the configuration
	 */
	protected function loadConfig()
	{
		$loader = new Config\Loader(__APP__.'/app/config');

		$config = $loader->load('api.yml');
		$config->compile();

		$this->container->set('config', $config);

		return $this;
	}

	/**
	 * Load services
	 */
	protected function loadServices()
	{
		// Doctrine
		$config = $this->container->get('config')->get('doctrine.dbal');
		$doctrine = \Doctrine\DBAL\DriverManager::getConnection($config);

		$this->container->set('doctrine', $doctrine);

		return $this;
	}

	/**
	 * Register the extensions
	 */
	protected function registerExtensions()
	{
		if ($this->debug) {
			$extensionsLoader = new Extension\FilesystemLoader(__APP__.'/src');
		} else {
			$extensionsLoader = new Extension\ApcLoader(__APP__.'/src');
		}

		$this->container->set('extensions', $extensionsLoader->load());

		return $this;
	}

	/**
	 * Load the extensions
	 */
	protected function loadExtensions()
	{
		AnnotationRegistry::registerAutoloadNamespaces(array(
			'Etu\Api\Annotations' => __API__.'/src/',
			'Swagger\Annotations' => __APP__.'/vendor/zircote/swagger-php/library/'
		));

		if (function_exists('apc_fetch')) {
			$reader = new CachedReader(new AnnotationReader(), new ApcCache(), $this->debug);
		} else {
			$reader = new AnnotationReader();
		}

		$this->container->set('annotations.reader', $reader);

		/** @var Extension\Extension[] $extensions */
		$extensions = $this->container->get('extensions');
		$routes = new RouteCollection();

		foreach ($extensions as $extension) {
			foreach ($extension->getResources() as $resource) {
				$reflection = new \ReflectionClass($resource);
				$annotations = $reader->getClassAnnotations($reflection);

				foreach ($annotations as $annotation) {
					if ($annotation instanceof Annotations\Resource) {
						$name = $annotation->getName();

						if (empty($name)) {
							$class = array_reverse(explode('\\', $resource));
							$name = StringManipulationExtension::uncamelize(str_replace('Resource', '', $class[0]));
						}

						$routes->add($name, new Route(
							$annotation->getPath(),
							array_merge($annotation->getDefaults(), array('resource' => $reflection)),
							$annotation->getRequirements()
						));
					}
				}
			}
		}

		$this->container->set('routing.routes', $routes);

		return $this;
	}

	/**
	 * @return bool
	 */
	protected function isValidSecurityToken(Request $request)
	{
		/** @var \Doctrine\DBAL\Connection $doctrine */
		$doctrine = $this->container->get('doctrine');

		// Check using $request->headers->get('EtuUTT-API-Token')
		if ($request->headers->has('EtuUTT-API-Token')) {
			$token = $request->headers->get('EtuUTT-API-Token');
		} elseif ($request->query->has('token')) {
			$token = $request->query->get('token');
		} elseif ($request->query->has('api_key')) {
			$token = $request->query->get('api_key');
		} else {
			return false;
		}

		$cacheDriver = new \Doctrine\Common\Cache\ApcCache();

		if ($cacheDriver->contains('etuutt_access_'.$token)) {
			$access = $cacheDriver->fetch('etuutt_access_'.$token);
		} else {
			$access = $doctrine->createQueryBuilder()
				->select('a.*')
				->from('etu_api_access', 'a')
				->where('a.token = :token')
				->setParameter('token', $token)
				->setMaxResults(1)
				->execute()
				->fetch(\PDO::FETCH_ASSOC);

			if ($access) {
				$cacheDriver->save('etuutt_access_'.$token, $access, 3600);
			}
		}

		if (! $access) {
			return false;
		}

		$securityAccess = new SecurityToken($access['id']);
		$securityAccess->setApplication($access['application']);
		$securityAccess->setAuthorization((int) $access['authorization']);
		$securityAccess->setToken($access['token']);

		$this->container->set('security.token', $securityAccess);

		return true;
	}
}
