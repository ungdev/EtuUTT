<?php

namespace Etu\Api\Extension;

class Extension
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $resources;

	/**
	 * @var array
	 */
	protected $models;

	/**
	 * @param array $models
	 * @return $this
	 */
	public function setModels($models)
	{
		$this->models = $models;
		return $this;
	}

	/**
	 * @param $model
	 * @return $this
	 */
	public function addModel($model)
	{
		$this->models[] = $model;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getModels()
	{
		return $this->models;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param array $resources
	 * @return $this
	 */
	public function setResources($resources)
	{
		$this->resources = $resources;
		return $this;
	}

	/**
	 * @param array $resource
	 * @return $this
	 */
	public function addResource($resource)
	{
		$this->resources[] = $resource;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getResources()
	{
		return $this->resources;
	}
}
