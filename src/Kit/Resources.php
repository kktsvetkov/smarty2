<?php

namespace Smarty2\Kit;

use function explode;
use function strlen;

class Resources
{
	/**
	* Parse the type and name from the resource; if resource type is
	* omitted, $defaultResourceType is used instead
	*
	* @param string $resourceName
	* @param string $defaultResourceType
	* @return array with two elements, type and name
	*/
	static function parseResourceName(string $resourceName, string $defaultResourceType = null) : array
	{
		// split by the first colon
		//
		$parsed = explode(':', $resourceName, 2);

		// no resource type given
		//
		if (count($parsed) == 1)
		{
			return array(
				$defaultResourceType ?? 'file',
				$parsed[0]
			);
		}

		// silly windows: 1 char is not resource type, but part of filepath
		//
		if (strlen($parsed[0]) == 1)
		{
			return array(
				$defaultResourceType ?? 'file',
				$resourceName
			);
		}

		return $parsed;
	}

}
