<?php

namespace Smarty2\Tests\Plugins\Modifiers;

use Smarty2\Tests\TestCase;

use function function_exists;
use function strpos;

class EscapeModifierTest extends TestCase
{
	function testGetPluginsFilepath()
	{
		$filepath = $this->smarty->_get_plugin_filepath('modifier', 'escape');

		$this->assertNotEmpty($filepath);
		$this->assertTrue(0 !== strpos($filepath, 'plugins/modifier.escape.php'));
	}

	function testIncludeFile()
	{
		$filepath = $this->smarty->_get_plugin_filepath('modifier', 'escape');

		$this->assertEquals( include($filepath), 1 );
	}

	function testFunctionExists()
	{
		$this->assertTrue(function_exists('smarty_modifier_escape'));
	}

	function testEscapeDefault()
	{
		$subject = smarty_modifier_escape("<html><body></body></html>");
		$this->assertEquals('&lt;html&gt;&lt;body&gt;&lt;/body&gt;&lt;/html&gt;',
			$subject);
	}

	function testEscapeHtml()
	{
		$subject = smarty_modifier_escape("<html><body></body></html>", 'html');
		$this->assertEquals('&lt;html&gt;&lt;body&gt;&lt;/body&gt;&lt;/html&gt;',
			$subject);
	}

	function testEscapeHtmlAll()
	{
		$subject = smarty_modifier_escape("<html><body></body></html>", 'htmlall');
		$this->assertEquals('&lt;html&gt;&lt;body&gt;&lt;/body&gt;&lt;/html&gt;',
			$subject);
	}

	function testEscapeUrl()
	{
		$subject = smarty_modifier_escape("http://test.com?foo=bar", 'url');
		$this->assertEquals('http%3A%2F%2Ftest.com%3Ffoo%3Dbar', $subject);
	}

	function testEscapeQuotes()
	{
		$subject = smarty_modifier_escape("'\\'\\''", 'quotes');
		$this->assertEquals("\\'\\'\\'\\'", $subject);
	}

	function testEscapeHex()
	{
		$subject = smarty_modifier_escape("abcd", 'hex');
		$this->assertEquals('%61%62%63%64', $subject);
	}

	function testEscapeHexEntity()
	{
		$subject = smarty_modifier_escape("ABCD", 'hexentity');
		$this->assertEquals('&#x41;&#x42;&#x43;&#x44;', $subject);
	}

	function testEscapeJavascript()
	{
		$subject = smarty_modifier_escape("\r\n\\", 'javascript');
		$this->assertEquals('\\r\\n\\\\', $subject);
	}

	function testEscapeArray()
	{
		$subject = smarty_modifier_escape(
			array('<b>Bold</b>', array('<i>Italic</i>'))
			);
		$this->assertEquals(
			array('&lt;b&gt;Bold&lt;/b&gt;', array('&lt;i&gt;Italic&lt;/i&gt;')),
			$subject);
	}

	function testEscapeObject()
	{
		$subject = smarty_modifier_escape(
			$original = (object) array('ID' => 123)
			);
		$this->assertEquals($original, $subject);
	}

	function testEscapeObjectToString()
	{
		// Exceptions have __toString() methods in them,
		// so they are good test subject for this case
		//
		$subject = smarty_modifier_escape(
			(new \Exception($original = '<em>Emphasis!</em>'))
			);

		$this->assertFalse( strpos($subject, $original) );
		$this->assertTrue(
			0 !== strpos($subject, '&lt;em&gt;Emphasis!&lt;/em&gt;')
			);
	}

}
