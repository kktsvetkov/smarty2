<?php

namespace Smarty2\Tests\Kit;

use PHPUnit\Framework\TestCase;
use Smarty2\Kit;

class ArgsTest extends TestCase
{
	function testDequoteSingleQuote()
	{
		$this->assertEquals('123', Kit\Args::dequote("'123'") );
		$this->assertEquals('\'123\' ', Kit\Args::dequote("'123' ") );
	}

	function testDequoteDoubleQuote()
	{
		$this->assertEquals('123', Kit\Args::dequote('"123"') );
		$this->assertEquals('"123" ', Kit\Args::dequote('"123" ') );
	}
}
