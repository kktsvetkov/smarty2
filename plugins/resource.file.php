<?php

function smarty_resource_file_source(string &$resource_name, ?string &$contents, $smarty) : bool
{
	$resource_name = smarty_resource_file_resolve($resource_name, $smarty);

	$contents = '';
	if (is_file($resource_name) && is_readable($resource_name))
	{
		$contents = file_get_contents($resource_name);
		return true;
	}

	return false;
}

function smarty_resource_file_timestamp(string &$resource_name, ?int &$timestamp, $smarty) : bool
{
	$resource_name = smarty_resource_file_resolve($resource_name, $smarty);

	$timestamp = 0;
	if (is_file($resource_name) && is_readable($resource_name))
	{
		if ($ts = filemtime($resource_name))
		{
			$timestamp = $ts;
			return true;
		}
	}

	return false;
}

function smarty_resource_file_secure() : bool
{
	return false;
}

function smarty_resource_file_trusted() : bool
{
	return false;
}

/**
* Append template folder to template name if it is a relative filepath
*
* @param string $resource_name
* @param smarty $smarty
*/
function smarty_resource_file_resolve(string $resource_name, $smarty) : string
{
	if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $resource_name))
	{
		// relative pathname to $smarty->template_dir
		//
		$fullpath = $smarty->template_dir . DIRECTORY_SEPARATOR . $resource_name;
		if (is_file($fullpath))
		{
			return $fullpath;
		}
	}

	return $resource_name;
}
