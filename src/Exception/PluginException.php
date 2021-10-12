<?php

namespace Smarty2\Exception;

use Smarty2\Exception\TemplateErrorTrait;
use UnexpectedValueException;

class PluginException extends UnexpectedValueException
{
	use TemplateErrorTrait;

	function __construct(string $message, string $templateName, int $templateLine)
	{
		$this->load($message, $templateName, $templateLine);
		parent::__construct( $this->getTemplateError() );
	}
}
