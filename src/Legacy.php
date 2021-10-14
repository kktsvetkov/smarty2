<?php

namespace Smarty2;

use Smarty2\Security\LegacyPolicy as SecurityPolicy;
use Smarty2\Exception;
use Smarty2\Resource;

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


	/**
	* Registers a resource to fetch a template
	*
	* @param string $type name of resource
	* @param array $functions array of functions to handle resource
	* @return self
	*/
	function register_resource(string $type, array $functions) : self
	{
		// With 4 elements the elements are the functions-callbacks
		// for the respective source, timestamp, secure and trusted
		// functions of the resource.
		//
		// Note: secure and trusted functions are no longer used
		//
		if (4 == count($functions))
		{
			$this->getResourceAggregate()->register($type,
				new Resource\CustomResource(
					$functions[0],	/* source */
					$functions[1],	/* timestamp */
					$this
					));
			return $this;
		}

		// With 5 elements the first element has to be an
		// object reference or a class name of the object
		// or class implementing the resource and the 4
		// following elements have to be the method names
		// implementing source, timestamp, secure and trusted.
		//
		// Note: secure and trusted functions are no longer used
		//
		if (5 == count($functions))
		{
			$this->getResourceAggregate()->register($type,
				new Resource\CustomResource(
					array(&$functions[0], $functions[1]), /* source */
					array(&$functions[0], $functions[2]),  /* timestamp */
					$this
					));

			return $this;
		}

		throw new Exception\ResourceException(
			"Malformed function-list for '{$type}' resource in "
				. __METHOD__ . '()'
			);
	}

	/**
	* Unregisters a resource
	*
	* @param string $type name of resource
	* @return self
	*/
	function unregister_resource($type) : self
	{
		$this->getResourceAggregate()->unregister($type);
		return $this;
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
