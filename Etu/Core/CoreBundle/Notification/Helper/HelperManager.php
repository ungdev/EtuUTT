<?php

namespace Etu\Core\CoreBundle\Notification\Helper;

/**
 * Helper manager.
 */
class HelperManager
{
    /**
     * @var array
     */
    protected $helpers = [];

    /**
     * @param array $helpers
     *
     * @return HelperManager
     */
    public function setHelpers($helpers)
    {
        $this->helpers = $helpers;

        return $this;
    }

    /**
     * @return $this
     */
    public function addHelper(HelperInterface $helper)
    {
        $this->helpers[$helper->getName()] = $helper;

        return $this;
    }

    /**
     * @param string $helperName
     *
     * @throws \InvalidArgumentException
     *
     * @return HelperInterface
     */
    public function getHelper($helperName)
    {
        if (isset($this->helpers[$helperName])) {
            return $this->helpers[$helperName];
        }

        throw new \InvalidArgumentException(sprintf('Render helper "%s" not found', $helperName));
    }

    /**
     * @return array
     */
    public function getHelpers()
    {
        return $this->helpers;
    }
}
