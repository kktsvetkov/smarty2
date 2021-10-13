<?php

namespace Smarty2;

use Smarty2\Security\LegacyPolicy as SecurityPolicy;

/**
* Legacy Smarty2 - the PHP template engine
*
* This class will keep all of the original methods including the deprecated ones
*
* @package Smarty2
*/
class Legacy extends Engine
{
	use Security\PolicyAwareTrait;

	/**
	* This enables template security. When enabled, many things are restricted
	* in the templates that normally would go unchecked. This is useful when
	* untrusted parties are editing templates and you want a reasonable level
	* of security. (no direct execution of PHP in templates for example)
	*
	* @var boolean
	*/
	public bool $security = false;

	/**
	* These are the security settings for Smarty. They are used only when
	* {@link Smarty2\Engine::$security} is enabled.
	*
	* @var array
	*/
	public array $security_settings  = array(
		'IF_FUNCS' => SecurityPolicy::DEFAULT_IF_FUNCS,
		'MODIFIER_FUNCS' => SecurityPolicy::DEFAULT_MODIFIER_FUNCS,
		'ALLOW_CONSTANTS' => false,
		'ALLOW_SUPER_GLOBALS' => true
		);

	/**
	* The class constructor.
	*/
	function __construct()
	{
		parent::__construct();
		$this->assign('SCRIPT_NAME', $_SERVER['SCRIPT_NAME'] ?? null);
	}

	function getSecurityPolicy() : SecurityPolicy
	{
		return $this->securityPolicy ??
			($this->securityPolicy = $this->loadSecurityPolicy());
	}

	protected function loadSecurityPolicy() : SecurityPolicy
	{
		$empty = array();
		$null = null;

		return $this->security
			? new SecurityPolicy(
				$this->security_settings['IF_FUNCS'],
				$this->security_settings['MODIFIER_FUNCS'],
				$this->security_settings['ALLOW_CONSTANTS'],
				$this->security_settings['ALLOW_SUPER_GLOBALS']
			)
			: new SecurityPolicy(
				$empty,
				$empty,
				$null,
				$null
			);
	}

	/**#@+
	* Deprecated Methods Section
	*/

	/**
	* {@deprecated}
	*/
	function clear_cache()
	{
		return false;
	}

	/**
	* {@deprecated}
	*/
	function clear_all_cache()
	{
		return false;
	}

	/**
	* {@deprecated}
	*/
	function is_cached()
	{
		return false;
	}

	/**
	* {@deprecated}
	*/
	function _get_auto_id()
	{
		return null;
	}

	/**
	* {@deprecated}
	*/
	function _read_file($filename)
	{
		if (!is_file($filename))
		{
			return '';
		}

		return file_get_contents($filename);
	}

	/**
	* {@deprecated}
	*/
	function &_smarty_cache_attrs()
	{
		static $dummy = [];
		return $dummy;
	}

	/**
	* {@deprecated}
	* @internal use Smarty2\Kit\Files::unlink() instead
	*/
	function _unlink($resource, $exp_time = null)
	{
		return Kit\Files::unlink($resource, $exp_time);
	}

	/**#@+
	* END Deprecated Methods Section
	*/
}
