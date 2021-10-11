<?php

namespace Smarty2\Kit;

use function filemtime;
use function is_file;
use function time;
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
}
