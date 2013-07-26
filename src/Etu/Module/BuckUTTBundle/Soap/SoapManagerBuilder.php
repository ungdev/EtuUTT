<?php

namespace Etu\Module\BuckUTTBundle\Soap;

use Symfony\Component\HttpFoundation\Session\Session;

class SoapManagerBuilder
{
	/**
	 * @var Session
	 */
	protected $session;

	/**
	 * @param Session $session
	 */
	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	/**
	 * @param $wsdlName
	 * @return SoapManager
	 */
	public function createManager($wsdlName)
    {
	    return new SoapManager($wsdlName, $this->session);
    }
}
