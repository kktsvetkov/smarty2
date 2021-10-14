<?php

namespace Smarty2\Resource;

use Smarty2\Exception;

trait CallbackResourceTrait
{
	protected $sourceCallback;
	protected $timestampCallback;

	protected function setSourceCallback(callable $sourceCallback)
	{
		$this->sourceCallback = $sourceCallback;
	}

	protected function setTimestampCallback(callable $timestampCallback)
	{
		$this->timestampCallback = $timestampCallback;
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
		return $sourceCallback( $name );
	}

	function getTemplateTimestamp(string $name) : integer
	{
		$timestampCallback = $this->timestampCallback;
		return $timestampCallback( $name );
	}

}
