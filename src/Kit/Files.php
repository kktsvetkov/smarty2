<?php

namespace Smarty2\Kit;

use function filemtime;
use function is_file;
use function time;
use function preg_match;
use function unlink;

class Files
{
	/**
	* unlink a file, possibly using expiration time
	*
	* @param string $resource
	* @param integer $exp_time
	*/
	static function unlink(string $resource, $exp_time = null)
	{
		if (!is_file($resource))
		{
			return false;
		}

		if (!empty($exp_time))
		{
			return (time() - filemtime($resource) >= $exp_time)
				? unlink($resource)
				: false;
		}

		return unlink($resource);
	}

	/**
	* Whether $filepath is an absolute file address
	* @return boolean
	*/
	static function isAbsolute(string $filepath) : bool
	{
		return preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $filepath);
	}

	/**
	* Whether $filepath is a relative file address 
	* @return boolean
	*/
	static function isRelative(string $filepath) : bool
	{
		return !self::isAbsolute($filepath);
	}
}
