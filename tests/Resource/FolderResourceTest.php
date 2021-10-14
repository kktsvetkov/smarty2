<?php

namespace Smarty2\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Smarty2\Resource\FolderResource;
use Smarty2\Exception;

use function trim;

class FolderResourceTest extends TestCase
{
	/**
	* @covers FolderResource::templateExists()
	* @covers FolderResource::resolveTemplateName()
	* @covers FolderResource::__construct()
	*/
	function testTemplateExists()
	{
		$resource = new FolderResource(
			__DIR__ . '/../templates'
			);

		$templates = array(
			'AssignTemplateVars.tpl' => true,
			'proba.tpl' => false,
			'Resources' => false,
			'Resources/' => false,
			'Resources/FolderResource.tpl' => true,
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
	* @covers FolderResource::getTemplateSource()
	* @covers FolderResource::resolveTemplateName()
	* @covers FolderResource::getRealFilepath()
	*/
	function testGetTemplateSource()
	{
		$resource = new FolderResource(
			__DIR__ . '/../templates'
			);
		$source = $resource->getTemplateSource('Resources/FolderResource.tpl');
		$this->assertEquals(
			trim( $source ),
			'{$smarty.template}'
		);
	}

	/**
	* @covers FolderResource::getTemplateSource()
	* @covers FolderResource::resolveTemplateName()
	* @covers FolderResource::getRealFilepath()
	*/
	function testGetTemplateSourceMissing()
	{
		$resource = new FolderResource(
			__DIR__ . '/../templates'
			);

		$this->expectException( Exception\TemplateNotFoundException::class );
		$resource->getTemplateSource('Resources/proba.tpl');
	}

	/**
	* @covers FolderResource::getTemplateTimestamp()
	* @covers FolderResource::resolveTemplateName()
	* @covers FolderResource::getRealFilepath()
	*/
	function testGetTemplateTimestamp()
	{
		$resource = new FolderResource(
			__DIR__ . '/../templates'
			);
		$timestamp = $resource->getTemplateTimestamp('Resources/FolderResource.tpl');
		$this->assertTrue( 0 !== $timestamp);
		$this->assertIsInt( $timestamp );
	}
}
