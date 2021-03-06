<?php

namespace Smarty2\Tests\Legacy;

use Smarty2\Legacy as Smarty;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

use function sys_get_temp_dir;

class TestCase extends PHPUnit_TestCase
{
	protected Smarty $smarty;

	function setUp() : void
	{
		$smarty = new Smarty;

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
