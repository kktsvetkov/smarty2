<?php

namespace Smarty2\Engine;

trait ConfigVarsTrait
{
	/**
	* @var array loaded configuration settings
	*/
	protected array $_config = array();

	/**
	* Returns an array containing config variables
	*
	* @param string $name
	* @return array
	*/
	function get_config_vars(string $name = null) : array
	{
		if (null === $name)
		{
			return $this->_config;
		}

		return $this->_config[$name] ?? [];
	}

	/**
	* Introduces values as config vars
	*
	* @param array|string $name the config var name
	* @param mixed $value the value to assign
	* @return self
	*/
	function set_config_vars($name, $value = null) : self
	{
		if (is_array($name))
		{
			foreach ($name as $key => $val)
			{
				if ($key != '')
				{
					$this->_config[$key] = $val;
				}
			}
		} else
		if ($name != '')
		{
			$this->_config[$name] = $value;
		}

		return $this;
	}

	/**
	* clear configuration values
	*
	* @param string $var
	* @return self
	*/
	function clear_config(string $var = null) : self
	{
		// clear all values
		//
		if(null === $var)
		{
			$this->_config = array();
		} else
		{
			unset($this->_config[$var]);
		}

		return $this;
	}
}
