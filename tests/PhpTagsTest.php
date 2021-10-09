<?php

namespace Smarty2\Tests;

use Smarty2\Engine;
use Smarty2\Tests\TestCase;

class PhpTagsTest extends TestCase
{
	/**
        * @covers Smarty2\Compiler::_compile_file()
	*/
	function testConstructorAssign()
	{
		$this->assertEquals(
			$this->smarty->fetch('PhpTagsTest.tpl'),
			'&lt;?php echo 123; ?&gt;'
		);
	}
}
