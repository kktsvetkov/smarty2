<?php

namespace Smarty2\Exception;

use InvalidArgumentException;

class TemplateNotFoundException extends InvalidArgumentException
{
	protected string $template;

	function __construct(string $message, string $template)
	{
		$this->template = $template;
		parent::__construct($message);
	}

	function getTemplate() : string
	{
		return $this->template;
	}
}
