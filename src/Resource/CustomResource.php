<?php

namespace Smarty2\Resource;

use Smarty2\Engine as Smarty;
use Smarty2\Exception;
use Smarty2\Resource\ResourceInterface;

class CustomResource implements ResourceInterface
{
	protected $sourceCallback;
	protected $timestampCallback;

	function __construct(
		callable $sourceCallback,
		callable $timestampCallback,
		Smarty $smarty)
	{
		$this->sourceCallback = $sourceCallback;
		$this->timestampCallback = $timestampCallback;
		$this->smarty = $smarty;
	}

	function templateExists(string $name) : bool
	{
		try {
			return $this->getTemplateSource($name)
				&& $this->getTemplateTimestamp($name);
		}
		catch (Exception\TemplateNotFoundException $e)
		{
		}

		return false;
	}

	function getTemplateSource(string $name) : string
	{
		$sourceCallback = $this->sourceCallback;
		$contents = '';

		$sourceCallback( $name, $contents, $this->smarty );
		return $contents;
	}

	function getTemplateTimestamp(string $name) : int
	{
		$timestampCallback = $this->timestampCallback;
		$timestamp = 0;

		$timestampCallback( $name, $timestamp, $this->smarty );
		return $timestamp;
	}
}
