<?php

namespace Smarty2\Tests\Legacy;

use Smarty2\Legacy;
use Smarty2\Tests\Legacy\TestCase;

class VarsTest extends TestCase
{
	/**
	* @covers Smarty2\Legacy::__construct()
	* @covers Smarty2\Legacy::assign()
	* @covers Smarty2\Legacy::get_template_vars()
	*/
	function testConstructorAssign()
	{
		$this->assertEquals(
			$this->smarty->get_template_vars('SCRIPT_NAME'),
			$_SERVER['SCRIPT_NAME']
		);
	}
}
