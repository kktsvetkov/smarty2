<?php

namespace Smarty2\Tests;

use Smarty2\Engine;
use Smarty2\Tests\TestCase;

class VarsTest extends TestCase
{
	/**
	* @covers Smarty2\Engine::__construct()
        * @covers Smarty2\Engine::assign()
        * @covers Smarty2\Engine::get_template_vars()
	*/
	function testConstructorAssign()
	{
		$this->assertEquals(
			$this->smarty->get_template_vars('SCRIPT_NAME'),
			$_SERVER['SCRIPT_NAME']
		);
	}
}
