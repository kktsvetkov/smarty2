<?php

/**
* Set SMARTY_DIR to absolute path to Smarty library files.
* Do it only if user application has not already defined it
*/
if (!defined('SMARTY_DIR'))
{
        define('SMARTY_DIR', __DIR__ . DIRECTORY_SEPARATOR);
}

/**
* Create class aliases for the ones form the namespace
*/
class_alias(Smarty2\Engine::class, 'Smarty');
class_alias(Smarty2\Compiler::class, 'Smarty_Compiler');
