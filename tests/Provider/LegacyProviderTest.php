<?php

namespace Smarty2\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Smarty2\Exception\FilepathException;
use Smarty2\Provider\LegacyProvider;

use const SMARTY_DIR;

use function realpath;
use function uniqid;

class LegacyProviderTest extends TestCase
{
	/**
	* @covers Smarty2\Provider\LegacyProvider::getPluginDirs()
	* @covers Smarty2\Provider\LegacyProvider::addPluginDir()
	* @covers Smarty2\Provider\LegacyProvider::__construct()
	*/
	function testConstructorWithDefaultEmptyArgument()
	{
		$provider = new LegacyProvider();
		$this->assertEquals(
			$provider->getPluginDirs(),
			array(realpath(SMARTY_DIR . '/plugins'))
		);
	}

	/**
	* @covers Smarty2\Provider\LegacyProvider::addPluginDir()
	* @covers Smarty2\Provider\LegacyProvider::__construct()
	*/
	function testConstructorWithSemigoodArgument()
	{
		$this->expectException(FilepathException::class);

		$provider = new LegacyProvider([
			SMARTY_DIR . '/plugins',
			'/tmp/proba/' . uniqid()
			]);
	}

	/**
	* @covers Smarty2\Provider\LegacyProvider::getPluginDirs()
	* @covers Smarty2\Provider\LegacyProvider::addPluginDir()
	*/
	function testAddPluginDir()
	{
		$provider = new LegacyProvider;
		$this->assertEquals($provider, $provider->addPluginDir(__DIR__));
		$this->assertEquals($provider->getPluginDirs(), $dirs = array(
			realpath(SMARTY_DIR . '/plugins'),
			__DIR__
		));

		// add the same dir second time
		//
		$provider->addPluginDir(__DIR__);
		$this->assertEquals($provider->getPluginDirs(), $dirs);
	}

	/**
	* @covers Smarty2\Provider\LegacyProvider::getPluginDirs()
	* @covers Smarty2\Provider\LegacyProvider::addPluginDir()
	*/
	function testAddPluginDirWithPrepend()
	{
		$provider = new LegacyProvider;
		$this->assertEquals($provider, $provider->addPluginDir(__DIR__, true));
		$this->assertEquals($provider->getPluginDirs(), $dirs = array(
			__DIR__,
			realpath(SMARTY_DIR . '/plugins'),

		));
	}

	/**
	* @covers Smarty2\Provider\LegacyProvider::getFilepath()
	*/
	function testGetFilepath()
	{
		$provider = new LegacyProvider;
		$this->assertEquals(
			$provider->getFilepath('modifier', 'escape'),
			realpath(SMARTY_DIR . '/plugins/modifier.escape.php')
		);
	}
}
