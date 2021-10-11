<?php

namespace Smarty2\Exception;

use RuntimeException;

class FilepathException extends RuntimeException
{
	protected string $filepath;

	function __construct(string $message, string $filepath)
	{
		$this->filepath = $filepath;
		parent::__construct($message);
	}

	function getFilepath() : string
	{
		return $this->filepath;
	}
}
