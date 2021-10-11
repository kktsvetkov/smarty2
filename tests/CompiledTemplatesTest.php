<?php

namespace Smarty2\Tests;

use Smarty2\Tests\TestCase;

use function is_file;
use function unlink;

class CompiledTemplatesTest extends TestCase
{
	/**
	* @covers Smarty2\Engine::clear_compiled_tpl()
	* @covers Smarty2\Engine::_get_compile_path()
	* @covers Smarty2\Engine::_is_compiled()
	*/
	function testClearCompiledTemplate()
	{
		$tpl_file = 'CompiledTemplatesTest.tpl';
		$smarty_compile_tpl = $this->smarty->_get_compile_path($tpl_file);

		// make sure we start fresh
		//
		if (is_file($smarty_compile_tpl))
		{
			unlink($smarty_compile_tpl);
		}
		$this->assertFalse( is_file($smarty_compile_tpl) );
		$this->assertFalse( $this->smarty->_is_compiled($tpl_file, $smarty_compile_tpl) );

		// check if the compiled file is there
		//
		$this->smarty->fetch($tpl_file);
		$this->assertTrue( is_file($smarty_compile_tpl) );
		$this->assertTrue( $this->smarty->_is_compiled($tpl_file, $smarty_compile_tpl) );

		// clean it up
		//
		$this->assertTrue( $this->smarty->clear_compiled_tpl($tpl_file) );
		$this->assertFalse( is_file($smarty_compile_tpl) );

		// are you sure it is cleaned ?
		//
		$this->assertFalse( $this->smarty->clear_compiled_tpl($tpl_file) );
		$this->assertFalse( is_file($smarty_compile_tpl) );
	}
}
