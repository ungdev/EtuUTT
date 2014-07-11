<?php

namespace Etu\Core\ApiBundle\Exception;

class ScopeNotFoundException extends \Exception
{
    /**
     * {@inheritDoc}
     */
    public function getMessageKey()
    {
        return 'Scope could not be found.';
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array(
            parent::serialize(),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($str)
    {
        list($parentData) = unserialize($str);

        parent::unserialize($parentData);
    }
}
