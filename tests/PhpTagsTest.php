<?php

namespace Smarty2\Tests;

use Smarty2\Engine;
use Smarty2\Tests\TestCase;

class PhpTagsTest extends TestCase
{
	/**
	* @covers Smarty2\Compiler::_compile_file()
	*/
	function testPHPTagsInTemplates()
	{
		$this->assertEquals(
			$this->smarty->fetch('PHPTagsInTemplates.tpl'),
			'&lt;?php echo 123; ?&gt;'
		);
	}

	/**
	* @covers Smarty2\Compiler::_compile_file()
	*/
	function testStripPhpBlockTags()
	{
		$this->assertEquals(
			$this->smarty->fetch('StripPhpBlockTags.tpl'),
			'123 () 345'
		);
	}
}
