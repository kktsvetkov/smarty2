<?php

namespace Smarty2\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Smarty2\Resource\FileResource;
use Smarty2\Exception;

use function trim;

class FileResourceTest extends TestCase
{
	/**
	* @covers FileResource::templateExists()
	* @covers FileResource::resolveTemplateName()
	* @covers FileResource::__construct()
	*/
	function testTemplateExists()
	{
		$resource = new FileResource(
			__DIR__ . '/../templates'
			);

		$templates = array(
			'AssignTemplateVars.tpl' => true,
			'proba.tpl' => false,
			'Resources' => false,
			'Resources/' => false,
			'Resources/FileResource.tpl' => true,
			'Resources/proba.html' => false,
			);
		foreach ($templates as $template => $result)
		{
			$this->assertEquals(
				$resource->templateExists($template),
				$result
			);
		}
	}

	/**
	* @covers FileResource::getTemplateSource()
	* @covers FileResource::resolveTemplateName()
	* @covers FileResource::getRealFilepath()
	*/
	function testGetTemplateSource()
	{
		$resource = new FileResource(
			__DIR__ . '/../templates'
			);
		$source = $resource->getTemplateSource('Resources/FileResource.tpl');
		$this->assertEquals(
			trim( $source ),
			'{$smarty.template}'
		);
	}

	/**
	* @covers FileResource::getTemplateSource()
	* @covers FileResource::resolveTemplateName()
	* @covers FileResource::getRealFilepath()
	*/
	function testGetTemplateSourceMissing()
	{
		$resource = new FileResource(
			__DIR__ . '/../templates'
			);

		$this->expectException( Exception\TemplateNotFoundException::class );
		$resource->getTemplateSource('Resources/proba.tpl');
	}

	/**
	* @covers FileResource::getTemplateTimestamp()
	* @covers FileResource::resolveTemplateName()
	* @covers FileResource::getRealFilepath()
	*/
	function testGetTemplateTimestamp()
	{
		$resource = new FileResource(
			__DIR__ . '/../templates'
			);
		$timestamp = $resource->getTemplateTimestamp('Resources/FileResource.tpl');
		$this->assertTrue( 0 !== $timestamp);
		$this->assertIsInt( $timestamp );
	}
}
