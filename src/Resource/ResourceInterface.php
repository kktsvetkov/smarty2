<?php

namespace Smarty2\Resource;

interface ResourceInterface
{
	function templateExists(string $name) : bool;
	function getTemplateSource(string $name) : string;
	function getTemplateTimestamp(string $name) : integer;
}
