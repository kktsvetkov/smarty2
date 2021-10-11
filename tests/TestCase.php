<?php

namespace Smarty2\Tests;

use Smarty2\Engine;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

use function sys_get_temp_dir;

class TestCase extends PHPUnit_TestCase
{
	protected Engine $smarty;

	function setUp() : void
	{
		$smarty = new Engine;

		$smarty->template_dir = __DIR__ . '/templates';

		$smarty->compile_dir = sys_get_temp_dir() . '/smarty.compiled';

		$smarty->compile_check = true;

		$this->smarty = $smarty;
	}

	function tearDown() : void
	{
		unset($this->smarty);
	}
}
