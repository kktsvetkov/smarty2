<?php

namespace Smarty2;

/**
* Legacy Smarty2 - the PHP template engine
*
* This class will keep all of the original methods including the deprecated ones
*
* @package Smarty2
*/
class Legacy extends Engine
{
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
