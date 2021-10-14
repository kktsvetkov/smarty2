<?php

namespace Smarty2\Depot;

interface DepotInterface
{
	function getCompiledFilename(string $name, string $compile_id = '') : string;

	function writeCompiled(string $compiled, string $contents) : bool;

	function getTimestamp(string $compiled) : int;
}
