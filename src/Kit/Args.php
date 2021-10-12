<?php

namespace Smarty2\Kit;

use function substr;

class Args
{
	/**
	* Remove starting and ending quotes from the string
	*
	* @param string $string
	* @return string
	*/
	static function dequote(string $string) : string
	{
		if ((substr($string, 0, 1) == "'" || substr($string, 0, 1) == '"')
			&& substr($string, -1) == substr($string, 0, 1))
		{
			return substr($string, 1, -1);
		}

		return $string;
	}
}
