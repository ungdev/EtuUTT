<?php

namespace Etu\Core\CoreBundle\Framework\Module;

use Doctrine\Common\Collections\ArrayCollection;
use Etu\Core\CoreBundle\Framework\Definition\Module;

/**
 * EtuUTT modules collection.
 */
class ModulesCollection extends ArrayCollection
{
    /**
     * Initializes a new ModulesCollection.
     *
     * @param Module[] $modules
     */
    public function __construct(array $modules = array())
    {
        $constructed = array();

        foreach ($modules as $module) {
            $constructed[$module->getIdentifier()] = $module;
        }

        parent::__construct($constructed);
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return $this->containsKey($identifier);
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function isEnabled($identifier)
    {
        if (!$this->has($identifier)) {
            return false;
        }

        return $this->get($identifier)->isEnabled();
    }
}
