<?php

namespace Smarty\Tests;

use Smarty2\Engine;
use PHPUnit\Framework\TestCase;

class VarsTest extends TestCase
{
        protected Engine $smarty;

        function setUp() : void
        {
                $this->smarty = new Engine;
        }

        function tearDown() : void
        {
                unset($this->smarty);
        }

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
