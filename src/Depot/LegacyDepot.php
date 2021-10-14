<?php

namespace Smarty2\Depot;

use Smarty2\Depot\DepotInterface;
use Smarty2\Exception\FilepathException;

use const LOCK_EX;

use function basename;
use function chmod;
use function crc32;
use function dirname;
use function filemtime;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function is_file;
use function is_writable;
use function mkdir;
use function rename;
use function sprintf;
use function str_replace;
use function tempnam;
use function urlencode;

class LegacyDepot implements DepotInterface
{
	protected bool $useSubDirs = false;

	protected string $compiledDir;

	function __construct(string $compiledDir, bool $useSubDirs = false)
	{
		$this->compiledDir = $compiledDir;
		$this->useSubDirs = $useSubDirs;
	}

	function getCompiledFilename(string $name, string $compile_id = '') : string
	{
		$separator =  $this->useSubDirs ? DIRECTORY_SEPARATOR : '^';
		$compiled = $this->compiledDir . DIRECTORY_SEPARATOR;

		// make compile_id safe for directory names
		//
		$compile_id = str_replace(
			'%7C', $separator, urlencode($compile_id)
			);

		$compiled .= $compile_id . $separator;

		// make source name safe for filename
		//
		$_filename = urlencode(basename($name));
		$_crc32 = sprintf('%08X', crc32($name));

		// prepend %% to avoid name conflicts
		//
		$_crc32 = substr($_crc32, 0, 2)
			. $separator . substr($_crc32, 0, 3)
			. $separator . $_crc32;
		$compiled .= '%%' . $_crc32 . '%%' . $_filename;

		return $compiled . '.php';
	}

	function writeCompiled(string $compiled, string $contents) : bool
	{
		$folder = dirname($compiled);
		if (!is_dir($folder))
		{
			if (file_exists($folder))
			{
				throw new FilepathException(
					"Compiled templates destination is not a folder: {$folder}",
					$folder
					);
			}

			if (false === mkdir($folder, 0771, true))
			{
				throw new FilepathException(
					"Failed to create folder '{$folder}'",
					$folder
					);
			}
		}

		if (!is_writable($folder))
		{
			throw new FilepathException(
				"Compiled template destination not writable: {$folder}",
				$folder
				);
		}

		$tmp = tempnam($folder, basename($compiled));
		if (false === file_put_contents($tmp, $contents, LOCK_EX))
		{
			throw new FilepathException(
				"Failed to write temporary file '{$tmp}'",
				$tmp
				);
		}

		if (false === rename($tmp, $compiled))
		{
			throw new FilepathException(
				"Failed to write file '{$compiled}'",
				$compiled
				);
		}

		chmod($compiled, 0644);
		return true;
	}

	function getTimestamp(string $compiled) : int
	{
		return (is_file($compiled))
			? (int) filemtime($compiled)
			: 0;
	}
}
