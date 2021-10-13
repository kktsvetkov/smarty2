<?php

namespace Smarty2\Security;

use Smarty2\Security\Policy as SecurityPolicy;

trait PolicyAwareTrait
{
	/**
	* @var Smarty2\Security\Policy
	*/
	protected SecurityPolicy $securityPolicy;

	function getSecurityPolicy() : SecurityPolicy
	{
		return $this->securityPolicy ?? ($this->securityPolicy = new SecurityPolicy);
	}

	function setSecurityPolicy(SecurityPolicy $securityPolicy)
	{
		$this->securityPolicy = $securityPolicy;
	}
}
