<?php

namespace Smarty2\Resource;

use Smarty2\Exception;
use Smarty2\Resource\ResourceInterface;

use const DIRECTORY_SEPARATOR;

use function file_get_contents;
use function filemtime;
use function is_file;
use function is_readable;
use function realpath;

class FolderResource implements ResourceInterface
{
	protected string $templateDir;

	function __construct(string $templateDir)
	{
		$this->templateDir = $templateDir;
	}

	function getTemplateDir() : string
	{
		return $this->templateDir;
	}

	function templateExists(string $name) : bool
	{
		$fullpath = $this->resolveTemplateName( $name );
		return is_file($fullpath) && is_readable($fullpath);
	}

	function getTemplateSource(string $name) : string
	{
		$fullpath = $this->getRealFilepath( $name );
		return file_get_contents($fullpath);
	}

	function getTemplateTimestamp(string $name) : int
	{
		$fullpath = $this->getRealFilepath( $name );
		return filemtime($fullpath);
	}

	protected function resolveTemplateName(string $name) : string
	{
		return $this->templateDir . DIRECTORY_SEPARATOR . $name;
	}

	protected function getRealFilepath(string $name) : string
	{
		$fullpath = $this->resolveTemplateName( $name );
		if (is_file($fullpath) && is_readable($fullpath))
		{
			return realpath($fullpath);
		}

		throw new Exception\TemplateNotFoundException(
			"Template not found: '{$name}' at {$fullpath}",
			$name
		);
	}
}
