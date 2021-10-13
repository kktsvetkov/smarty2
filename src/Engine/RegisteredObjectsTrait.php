<?php

namespace Smarty2\Engine;

use Smarty2\Exception;

trait RegisteredObjectsTrait
{
	/**
	* @var array registered objects
	*/
	protected array $_reg_objects = array();

	/**
	* Registers object to be used in templates
	*
	* @param string $object name of template object
	* @param object &$object_impl the referenced PHP object to register
	* @param null|array $allowed list of allowed methods (empty = all)
	* @param boolean $smarty_args smarty argument format, else traditional
	* @param null|array $block_functs list of methods that are block format
	*/
	function register_object(
		$object,
		&$object_impl,
		$allowed = array(),
		$smarty_args = true,
		$block_methods = array() )
	{
		settype($allowed, 'array');
		settype($smarty_args, 'boolean');

		$this->_reg_objects[$object] = array(
			&$object_impl,
			$allowed,
			$smarty_args,
			$block_methods
			);
	}

	/**
	* Unregisters object
	*
	* @param string $object name of template object
	* @return self
	*/
	function unregister_object($object) : self
	{
		unset($this->_reg_objects[$object]);
		return $this;
	}

	/**
	* Returns a reference to a registered object
	*
	* @param string $name
	* @return object
	*/
	function &get_registered_object(string $name)
	{
		if (!isset($this->_reg_objects[$name]))
		{
			throw new Exception\RegisteredObjectException(
				"'$name' is not a registered object"
			);
		}

		if (!is_object($this->_reg_objects[$name][0]))
		{
			throw new Exception\RegisteredObjectException(
				"registered '$name' is not an object"
			);
		}

		return $this->_reg_objects[$name][0];
	}
}
