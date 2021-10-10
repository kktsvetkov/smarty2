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

	/**
	* @covers Smarty2\Engine::clear_assign()
        * @covers Smarty2\Engine::assign()
        * @covers Smarty2\Engine::get_template_vars()
	*/
	function testAssignScalars()
	{
		// start fresh
		//
		$this->smarty->clear_assign('foo');
		$this->assertNull($this->smarty->get_template_vars('foo'));

		// first assign
		//
		$this->smarty->assign('foo', 'bar');
		$this->assertEquals($this->smarty->get_template_vars('foo'), 'bar');

		// overwrite it and test again
		//
		$this->smarty->assign('foo', 'bar!');
		$this->assertEquals($this->smarty->get_template_vars('foo'), 'bar!');

		// clean up and test again
		//
		$this->smarty->clear_assign('foo');
		$this->assertNull($this->smarty->get_template_vars('foo'));
	}

	/**
	* @covers Smarty2\Engine::clear_assign()
        * @covers Smarty2\Engine::assign()
        * @covers Smarty2\Engine::get_template_vars()
	*/
	function testAssignArrays()
	{
		// start fresh
		//
		$this->smarty->clear_assign('foo');
		$this->assertNull($this->smarty->get_template_vars('foo'));

		// first array assign
		//
		$this->smarty->assign(array('foo' => 'bar'));
		$this->assertEquals($this->smarty->get_template_vars('foo'), 'bar');

		// overwrite and test again
		//
		$this->smarty->assign(array('foo' => 'bar!'));
		$this->assertEquals($this->smarty->get_template_vars('foo'), 'bar!');
	}

	/**
        * @covers Smarty2\Engine::assign()
        * @covers Smarty2\Engine::get_template_vars()
	*/
	function testAssignTemplateVars()
	{
		$this->smarty->assign('foo', 'bar');
		$this->assertEquals(
			$this->smarty->fetch('AssignTemplateVars.tpl'),
			'bar'
			);

		$this->smarty->assign('foo', '');
		$this->assertEquals(
			$this->smarty->fetch('AssignTemplateVars.tpl'),
			''
			);

		$this->smarty->assign('foo', array());
		$this->assertEquals(
			$this->smarty->fetch('AssignTemplateVars.tpl'),
			'Array'
			);

		$this->smarty->assign('foo', new \stdClass);
		$this->assertEquals(
			$this->smarty->fetch('AssignTemplateVars.tpl'),
			''
			);

		$this->smarty->assign('foo', new class()
		{
			function __toString()
			{
				return 'Proba!';
			}
		});
		$this->assertEquals(
			$this->smarty->fetch('AssignTemplateVars.tpl'),
			'Proba!'
			);
	}
}
