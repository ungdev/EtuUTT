<?php

namespace Etu\Core\CoreBundle\Framework\Module;

use Doctrine\Common\Collections\ArrayCollection;
use Etu\Core\CoreBundle\Framework\Definition\Permission;

/**
 * EtuUTT permissions collection.
 */
class PermissionsCollection extends ArrayCollection
{
    /**
     * @throws \RuntimeException
     */
    public function __construct(array $permissions = [])
    {
        $constructed = [];

        foreach ($permissions as $permission) {
            if (!$permission instanceof Permission) {
                throw new \RuntimeException(sprintf('PermissionsCollection must contains only Permission objects (%s given)', gettype($permission)));
            }

            $constructed[$permission->getName()] = $permission;
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
}
