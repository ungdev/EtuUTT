<?php

namespace Etu\Core\CoreBundle\Framework\Exception;

/**
 * This exception is thrown when a non-existent module is requested.
 */
class ModuleNotFoundException extends \InvalidArgumentException
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $sourceId;

	/**
	 * @param string $id
	 * @param string $sourceId
	 */
	public function __construct($id, $sourceId = null)
    {
        if (null === $sourceId) {
            $msg = sprintf('You have requested a non-existent module "%s".', $id);
        } else {
            $msg = sprintf('The module "%s" has a dependency on a non-existent module "%s".', $sourceId, $id);
        }

        parent::__construct($msg);

        $this->id = $id;
        $this->sourceId = $sourceId;
    }

	/**
	 * @return string
	 */
	public function getId()
    {
        return $this->id;
    }

	/**
	 * @return string
	 */
	public function getSourceId()
    {
        return $this->sourceId;
    }
}
