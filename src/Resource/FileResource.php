<?php

namespace Smarty2\Resource;

use Smarty2\Resource\ResourceInterface;

use const DIRECTORY_SEPARATOR;

use function file_get_contents;
use function filemtime;
use function is_file;
use function is_readable;

class FileResource implements ResourceInterface
{
	protected string $templateDir;

	function __construct(string $templateDir)
	{
		$this->templateDir = $templateDir;
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

	function getTemplateTimestamp(string $name) : integer
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
			throw new \InvalidArgumentException(
				"Template not found: '{$name}' at {$fullpath}"
			);
		}

		return $filepath;
	}
}
