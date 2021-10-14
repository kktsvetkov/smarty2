<?php

namespace Smarty2\Resource;

use Smarty2\Engine as Smarty;
use Smarty2\Exception;
use Smarty2\Resource\ResourceInterface;
use Smarty2\Resource\CallbackResourceTrait;

use function function_exists;

class PluginResource implements ResourceInterface
{
	use CallbackResourceTrait;

	function __construct(string $type, Smarty $smarty)
	{
		$this->loadResourcePlugin($type, $smarty);
	}

	/**
	* Load a resource plugin
	*
	* @param string $type
	* @return boolean
	* @throws Smarty2\Exception\ResourceException
	*/
	protected function loadResourcePlugin(string $type, Smarty $smarty)
	{
		/*
		* Resource plugins are not quite like the other ones, so they are
		* handled differently. The first element of plugin info is the array of
		* functions provided by the plugin, the second one indicates whether
		* all of them exist or not.
		*/

		// load from resource.$type.php file
		//
		$plugin_resource_file = $smarty->_get_plugin_filepath('resource', $type);
		if ($plugin_resource_file)
		{
			throw new Exception\ResourceException(
				"Resource '{$type}' is not implemented as resource plugin"
				);
		}

		/*
		* If the plugin file is found, it -must- provide the
		* properly named plugin functions.
		*/
		include_once($plugin_resource_file);

		$sourceCallback = "smarty_resource_{$type}_source";
		if (!function_exists($sourceCallback))
		{
			throw new Exception\ResourceException(
				"Function {$sourceCallback}() not found in {$plugin_resource_file}"
				);
		}

		$this->setSourceCallback($sourceCallback);

		$timestampCallback = "smarty_resource_{$type}_timestamp";
		if (!function_exists($timestampCallback))
		{
			throw new Exception\ResourceException(
				"Function {$timestampCallback}() not found in {$plugin_resource_file}"
				);
		}

		$this->setTimestampCallback($timestampCallback);
	}
}
