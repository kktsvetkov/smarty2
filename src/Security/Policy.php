<?php

namespace Smarty2\Security;

use const E_USER_WARNING;

use function in_array;
use function trigger_error;

class Policy
{
	const DEFAULT_IF_FUNCS = array(
		'array', 'list',
		'isset', 'empty',
		'count', 'sizeof',
		'in_array', 'is_array',
		'true', 'false', 'null'
		);

	const DEFAULT_MODIFIER_FUNCS = array('count');

	protected array $funcsIfs = array();
	protected array $funcsModifiers = array();
	protected ?bool $allowConstants = null;
	protected ?bool $allowSuperGlobals = null;

	function __construct(
		array $funcsIfs = [],
		array $funcsModifiers = [],
		bool $allowConstants = null,
		bool $allowSuperGlobals = null)
	{
		$this->funcsIfs = $funcsIfs;
		$this->funcsModifiers = $funcsModifiers;
		$this->allowConstants = $allowConstants;
		$this->allowSuperGlobals = $allowSuperGlobals;
	}

	/**
	* Trigger an warning-level error message
	*
	* @param string $message
	* @param string $template
	* @param integer $line
	*/
	function issueWarning(string $message, ?string $template, ?int $line)
	{
		trigger_error(
			"(security) {$message} [in {$template} line {$line}]",
			E_USER_WARNING
		);
	}

	function isIfFunctionAllowed(string $function) : bool
	{
		if (empty($this->funcsIfs))
		{
			return true;
		}

		return in_array($function, $this->funcsIfs);
	}

	function isModifierAllowed(string $modifier) : bool
	{
		if (empty($this->funcsModifiers))
		{
			return true;
		}

		return in_array($modifier, $this->funcsModifiers);
	}

	function areConstantsAllowed() : bool
	{
		return $this->allowConstants ?? true;
	}

	function areSuperGlobalsAllowed() : bool
	{
		return $this->allowSuperGlobals ?? true;
	}

}
