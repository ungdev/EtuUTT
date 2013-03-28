<?php

namespace Etu\Core\CoreBundle\Notification\Helper;

/**
 * Helper manager
 */
class HelperManager
{
	/**
	 * @var array
	 */
	protected $helpers = array();

	/**
	 * @param array $helpers
	 * @return HelperManager
	 */
	public function setHelpers($helpers)
	{
		$this->helpers = $helpers;

		return $this;
	}

	/**
	 * @param HelperInterface $helper
	 * @return $this
	 */
	public function addHelper(HelperInterface $helper)
	{
		$this->helpers[$helper->getName()] = $helper;

		return $this;
	}

	/**
	 * @param string $helperName
	 * @return HelperInterface
	 * @throws \InvalidArgumentException
	 */
	public function getHelper($helperName)
	{
		if (isset($this->helpers[$helperName])) {
			return $this->helpers[$helperName];
		}

		throw new \InvalidArgumentException(sprintf(
			'Notification render help "%s" no found', $helperName
		));
	}

	/**
	 * @return array
	 */
	public function getHelpers()
	{
		return $this->helpers;
	}
}