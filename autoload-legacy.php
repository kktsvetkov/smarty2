<?php

/**
* Set SMARTY_DIR to absolute path to Smarty library files.
* Do it only if user application has not already defined it
*/
if (!defined('SMARTY_DIR'))
{
        define('SMARTY_DIR', __DIR__ . DIRECTORY_SEPARATOR);
}

define('SMARTY_PHP_PASSTHRU',   0);
define('SMARTY_PHP_QUOTE',      1);
define('SMARTY_PHP_REMOVE',     2);
define('SMARTY_PHP_ALLOW',      3);

/**
* Create class aliases for the ones form the namespace
*/
class_alias(Smarty2\Engine::class, 'Smarty');
class_alias(Smarty2\Compiler::class, 'Smarty_Compiler');
