<?php

namespace Smarty2\Tests;

use Smarty2\Tests\TestCase;

use function is_file;
use function unlink;

class TemplatesTest extends TestCase
{
	/**
	* @covers Smarty2\Engine::template_exists()
	*/
	function testClearCompiledTemplate()
	{
		$templates = array(
			'StripPhpBlockTags.tpl' => true,
			'PHPTagsInTemplates.tpl' => true,
			'Resources/FolderResource.tpl' => true,
			'proba.tpl' => false,
			'Resurces' => false,
			'Resources/' => false,
			'Resources/proba.tpl' => false,
			);
		foreach ($templates as $tpl_file => $result)
		{
			$this->assertEquals(
				$this->smarty->template_exists($tpl_file),
			 	$result
			);
		}
	}
}
