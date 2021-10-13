<?php

namespace Smarty2\Security;

use Smarty2\Security\Policy as SecurityPolicy;

class LegacyPolicy extends SecurityPolicy
{
	/**
	* Keep references to arguments
	*
	* {@inheritdoc}
	*/
	function __construct(
		array &$funcsIfs,
		array &$funcsModifiers,
		?bool &$allowConstants,
		?bool &$allowSuperGlobals)
	{
		$this->funcsIfs =& $funcsIfs;
		$this->funcsModifiers =& $funcsModifiers;
		$this->allowConstants =& $allowConstants;
		$this->allowSuperGlobals =& $allowSuperGlobals;
	}
}
