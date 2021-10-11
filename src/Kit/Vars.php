<?php

namespace Smarty2\Kit;

use function is_array;
use function is_callable;
use function is_object;

class Vars
{
	/**
	* Always returns a string for what is inside $subject
	*
	* @param mixed $subject
	* @return string
	*/
	static function toString($subject) : string
	{
		// return only objects that can be made into strings
		//
		if (is_object($subject))
		{
			return is_callable([$subject, '__toString'])
				? $subject->__toString()
				: '';
		}

		// there isn't a good way to cast an array as string
		//
		if (is_array($subject))
		{
			return 'Array';
		}

		return (string) $subject;
	}
}
