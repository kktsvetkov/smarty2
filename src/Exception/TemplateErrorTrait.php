<?php

namespace Smarty2\Exception;

use function sprintf;

trait TemplateErrorTrait
{
	protected string $templateMessage;
	protected string $templateName;
	protected int $templateLine;

	protected function load(string $message, string $templateName, int $templateLine)
	{
		$this->templateMessage = $message;
		$this->templateName = $templateName;
		$this->templateLine = $templateLine;
	}

	function getTemplateError() : string
	{
		return sprintf(
			'%s [in %s line %d]',
			$this->templateMessage,
			$this->templateName,
			$this->templateLine
		);
	}

	function getTemplateMessage() : string
	{
		return $this->templateMessage;
	}

	function getTemplateName() : string
	{
		return $this->templateName;
	}

	function getTemplteLine() : int
	{
		return $this->templateLine;
	}
}
