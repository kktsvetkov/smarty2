<?php

namespace Smarty2\Resource;

use Smarty2\Exception;
use Smarty2\Resource\ResourceInterface;
use Smarty2\Resource\CallbackResourceTrait;

class CustomResource implements ResourceInterface
{
	use CallbackResourceTrait;

	function __construct(callable $sourceCallback, callable $timestampCallback)
	{
		$this->setSourceCallback($sourceCallback);
		$this->setTimestampCallback($timestampCallback);
	}
}
