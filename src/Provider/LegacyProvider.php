<?php

namespace Smarty2\Provider;

use Smarty2\Exception\FilepathException;

use const DIRECTORY_SEPARATOR;
use const SMARTY_DIR;

use function array_push;
use function array_unshift;
use function dirname;
use function in_array;
use function is_dir;
use function is_file;
use function is_readable;
use function realpath;

class LegacyProvider
{
	/**
	* @var array list of directories searched for plugin
	*/
	protected array $pluginDirs = array();

	/**
	* Constructor
	*
	* @param array $pluginDirs
	*/
	function __construct(array $pluginDirs = null)
	{
		$pluginDirs = $pluginDirs ?? array( SMARTY_DIR . '/plugins' );
		foreach ($pluginDirs as $dir)
		{
			$this->addPluginDir($dir);
		}
	}

	/**
	* Get the list of plugin directories
	* @return array
	*/
	function getPluginDirs() : array
	{
		return $this->pluginDirs;
	}

	/**
	* Add a new plugin directory
	*
	* @param string $folder
	* @param bool $prepent should we add it at the end of the list or in front of it
	* @return self
	* @throws FilepathException
	*/
	function addPluginDir(string $folder, bool $prepend = false) : self
	{
		if (!is_dir($folder))
		{
			throw new FilepathException(
				"Not a directory: {$folder}",
				$folder
			);
		}

		$dir = realpath($folder);
		if (!in_array($dir, $this->pluginDirs))
		{
			$prepend
				? array_unshift($this->pluginDirs, $dir)
				: array_push($this->pluginDirs, $dir);
		}

		return $this;
	}

	/**
	* @var array plugin filepath cache
	*/
	protected array $pluginCache = array();

	/**
	* Get filepath to plugin
	*
	* @param string $type
	* @param string $name
	* @return string
	*/
	function getFilepath(string $type, string $name) : string
	{
		if (isset($this->pluginCache[ $type ][ $name ]))
		{
			return $this->pluginCache[ $type ][ $name ];
		}

		$pluginFilename = "{$type}.{$name}.php";
		foreach ($this->pluginDirs as $dir)
		{
			$filepath = $dir . DIRECTORY_SEPARATOR . $pluginFilename;
			if (is_file($filepath) && is_readable($filepath))
			{
				return $this->pluginCache[ $type ][ $name ] = $filepath;
			}
		}

		return $this->pluginCache[ $type ][ $name ] = '';
	}
}
