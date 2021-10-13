<?php

namespace Smarty2;

use Smarty2\Exception;
use Smarty2\Security as SmartySecurity;

/**
* Smarty2 - the PHP template engine
*
* @package Smarty2
*/
class Engine
{
	use Engine\ConfigVarsTrait;
	use Engine\RegisteredObjectsTrait;

	/**
	* Smarty version number
	*
	* @var string
	*/
	var $_version = '2.7.2';

    /**#@+
     * Smarty Configuration Section
     */

    /**
     * The name of the directory where templates are located.
     *
     * @var string
     */
    public string $template_dir    =  'templates';

    /**
     * The directory where compiled templates are located.
     *
     * @var string
     */
    public string $compile_dir     =  'templates_c';

    /**
     * An array of directories searched for plugins.
     *
     * @var array
     */
    public array $plugins_dir     =  array('plugins');

    /**
     * If debugging is enabled, debugging stats will be collected in
     * {@link Smarty::$_smarty_debug_info}
     *
     * @var boolean
     */
    public bool $debugging       =  false;

    /**
     * When set, smarty does uses this value as error_reporting-level.
     *
     * @var integer
     */
    public ?int $error_reporting  =  null;

    /**
     * This tells Smarty whether to check for recompiling or not. Recompiling
     * does not need to happen unless a template or config file is changed.
     * Typically you enable this during development, and disable for
     * production.
     *
     * @var boolean
     */
    public bool $compile_check   =  true;

    /**
     * This forces templates to compile every time. Useful for development
     * or debugging.
     *
     * @var boolean
     */
    public bool $force_compile   =  false;

	/**
	* This enables template security. When enabled, many things are restricted
	* in the templates that normally would go unchecked. This is useful when
	* untrusted parties are editing templates and you want a reasonable level
	* of security. (no direct execution of PHP in templates for example)
	*
	* @var boolean
	*/
	public bool $security = false;

	/**
	* These are the security settings for Smarty. They are used only when
	* {@link Smarty2\Engine::$security} is enabled.
	*
	* @var array
	*/
	public array $security_settings  = array(
		'IF_FUNCS' => SmartySecurity::DEFAULT_IF_FUNCS,
		'MODIFIER_FUNCS' => SmartySecurity::DEFAULT_MODIFIER_FUNCS,
		'ALLOW_CONSTANTS' => false,
		'ALLOW_SUPER_GLOBALS' => true
		);

    /**
     * The left delimiter used for the template tags.
     *
     * @var string
     */
    public string $left_delimiter  =  '{';

    /**
     * The right delimiter used for the template tags.
     *
     * @var string
     */
    public string $right_delimiter =  '}';

    /**
     * Set this if you want different sets of compiled files for the same
     * templates. This is useful for things like different languages.
     * Instead of creating separate sets of templates per language, you
     * set different compile_ids like 'en' and 'de'.
     *
     * @var string
     */
    public string $compile_id = '';

    /**
     * This tells Smarty whether or not to use sub dirs in the
     * templates_c/ directories. sub directories better organized, but
     * may not work well with PHP safe mode enabled.
     *
     * @var boolean
     *
     */
    public bool $use_sub_dirs	  = false;

    /**
     * This is a list of the modifiers to apply to all template variables.
     * Put each modifier in a separate array element in the order you want
     * them applied. example: <code>array('escape:"htmlall"');</code>
     *
     * @var array
     */
    public array $default_modifiers	= array();

    /**
     * This is the resource type to be used when not specified
     * at the beginning of the resource path. examples:
     * $smarty->display('file:index.tpl');
     * $smarty->display('db:index.tpl');
     * $smarty->display('index.tpl'); // will use default resource type
     * {include file="file:index.tpl"}
     * {include file="db:index.tpl"}
     * {include file="index.tpl"} {* will use default resource type *}
     *
     * @var array
     */
    public string $default_resource_type    = 'file';

    /**
     * This indicates which filters are automatically loaded into Smarty.
     *
     * @var array array of filter names
     */
    public array $autoload_filters = array();

    /**
     * If a template cannot be found, this PHP function will be executed.
     * Useful for creating templates on-the-fly or other special action.
     *
     * @var string function name
     */
    var $default_template_handler_func = '';

    /**
     * The class used for compiling templates.
     *
     * @var string
     */
    public string $compiler_class = Compiler::class;

/**#@+
 * END Smarty Configuration Section
 * There should be no need to touch anything below this line.
 * @access private
 */
    /**
     * where assigned template vars are kept
     *
     * @var array
     */
    var $_tpl_vars	     = array();

    /**
     * stores run-time $smarty.* vars
     *
     * @var null|array
     */
    var $_smarty_vars	  = null;

    /**
     * keeps track of sections
     *
     * @var array
     */
    var $_sections	     = array();

    /**
     * keeps track of foreach blocks
     *
     * @var array
     */
    var $_foreach	      = array();

    /**
     * keeps track of tag hierarchy
     *
     * @var array
     */
    var $_tag_stack	    = array();

    /**
     * current template inclusion depth
     *
     * @var integer
     */
    var $_inclusion_depth      = 0;

    /**
     * for different compiled templates
     *
     * @var string
     */
    var $_compile_id	   = null;

    /**
     * collected debugging information
     *
     * @var array
     */
    var $_smarty_debug_info    = array();

    /**
     * table keeping track of plugins
     *
     * @var array
     */
    var $_plugins	      = array(
				       'modifier'      => array(),
				       'function'      => array(),
				       'block'	 => array(),
				       'compiler'      => array(),
				       'prefilter'     => array(),
				       'postfilter'    => array(),
				       'outputfilter'  => array(),
				       'resource'      => array(),
				       'insert'	=> array());
    /**#@-*/

    /**
     * The class constructor.
     */
    public function __construct()
    {
      $this->assign('SCRIPT_NAME', $_SERVER['SCRIPT_NAME'] ?? null);
    }

    /**
     * assigns values to template variables
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed $value the value to assign
     */
    function assign($tpl_var, $value = null)
    {
	if (is_array($tpl_var)){
	    foreach ($tpl_var as $key => $val) {
		if ($key != '') {
		    $this->_tpl_vars[$key] = $val;
		}
	    }
	} else {
	    if ($tpl_var != '')
		$this->_tpl_vars[$tpl_var] = $value;
	}
    }

    /**
     * assigns values to template variables by reference
     *
     * @param string $tpl_var the template variable name
     * @param mixed $value the referenced value to assign
     */
    function assign_by_ref($tpl_var, &$value)
    {
	if ($tpl_var != '')
	    $this->_tpl_vars[$tpl_var] = &$value;
    }

    /**
     * appends values to template variables
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed $value the value to append
     */
    function append($tpl_var, $value=null, $merge=false)
    {
	if (is_array($tpl_var)) {
	    // $tpl_var is an array, ignore $value
	    foreach ($tpl_var as $_key => $_val) {
		if ($_key != '') {
		    if(!@is_array($this->_tpl_vars[$_key])) {
			settype($this->_tpl_vars[$_key],'array');
		    }
		    if($merge && is_array($_val)) {
			foreach($_val as $_mkey => $_mval) {
			    $this->_tpl_vars[$_key][$_mkey] = $_mval;
			}
		    } else {
			$this->_tpl_vars[$_key][] = $_val;
		    }
		}
	    }
	} else {
	    if ($tpl_var != '' && isset($value)) {
		if(!@is_array($this->_tpl_vars[$tpl_var])) {
		    settype($this->_tpl_vars[$tpl_var],'array');
		}
		if($merge && is_array($value)) {
		    foreach($value as $_mkey => $_mval) {
			$this->_tpl_vars[$tpl_var][$_mkey] = $_mval;
		    }
		} else {
		    $this->_tpl_vars[$tpl_var][] = $value;
		}
	    }
	}
    }

    /**
     * appends values to template variables by reference
     *
     * @param string $tpl_var the template variable name
     * @param mixed $value the referenced value to append
     */
    function append_by_ref($tpl_var, &$value, $merge=false)
    {
	if ($tpl_var != '' && isset($value)) {
	    if(!@is_array($this->_tpl_vars[$tpl_var])) {
	     settype($this->_tpl_vars[$tpl_var],'array');
	    }
	    if ($merge && is_array($value)) {
		foreach($value as $_key => $_val) {
		    $this->_tpl_vars[$tpl_var][$_key] = &$value[$_key];
		}
	    } else {
		$this->_tpl_vars[$tpl_var][] = &$value;
	    }
	}
    }


    /**
     * clear the given assigned template variable.
     *
     * @param string $tpl_var the template variable to clear
     */
    function clear_assign($tpl_var)
    {
	if (is_array($tpl_var))
	    foreach ($tpl_var as $curr_var)
		unset($this->_tpl_vars[$curr_var]);
	else
	    unset($this->_tpl_vars[$tpl_var]);
    }


    /**
     * Registers custom function to be used in templates
     *
     * @param string $function the name of the template function
     * @param string $function_impl the name of the PHP function to register
     */
    function register_function($function, $function_impl )
    {
	$this->_plugins['function'][$function] =
	    array($function_impl, null, null, false);

    }

    /**
     * Unregisters custom function
     *
     * @param string $function name of template function
     */
    function unregister_function($function)
    {
	unset($this->_plugins['function'][$function]);
    }

    /**
     * Registers block function to be used in templates
     *
     * @param string $block name of template block
     * @param string $block_impl PHP function to register
     */
    function register_block($block, $block_impl)
    {
	$this->_plugins['block'][$block] =
	    array($block_impl, null, null, false);
    }

    /**
     * Unregisters block function
     *
     * @param string $block name of template function
     */
    function unregister_block($block)
    {
	unset($this->_plugins['block'][$block]);
    }

    /**
     * Registers compiler function
     *
     * @param string $function name of template function
     * @param string $function_impl name of PHP function to register
     */
    function register_compiler_function($function, $function_impl)
    {
	$this->_plugins['compiler'][$function] =
	    array($function_impl, null, null, false);
    }

    /**
     * Unregisters compiler function
     *
     * @param string $function name of template function
     */
    function unregister_compiler_function($function)
    {
	unset($this->_plugins['compiler'][$function]);
    }

    /**
     * Registers modifier to be used in templates
     *
     * @param string $modifier name of template modifier
     * @param string $modifier_impl name of PHP function to register
     */
    function register_modifier($modifier, $modifier_impl)
    {
	$this->_plugins['modifier'][$modifier] =
	    array($modifier_impl, null, null, false);
    }

    /**
     * Unregisters modifier
     *
     * @param string $modifier name of template modifier
     */
    function unregister_modifier($modifier)
    {
	unset($this->_plugins['modifier'][$modifier]);
    }

	/**
	* Registers a resource to fetch a template
	*
	* @param string $type name of resource
	* @param array $functions array of functions to handle resource
	* @return self
	*/
	function register_resource(string $type, array $functions) : self
	{
		// With 4 elements the elements are the functions-callbacks
		// for the respective source, timestamp, secure and trusted
		// functions of the resource.
		//
		// Note: secure and trusted functions are no longer used
		//
		if (4 == count($functions))
		{
			$this->_plugins['resource'][$type] = array($functions, false);
			return $this;
		}

		// With 5 elements the first element has to be an
		// object reference or a class name of the object
		// or class implementing the resource and the 4
		// following elements have to be the method names
		// implementing source, timestamp, secure and trusted.
		//
		// Note: secure and trusted functions are no longer used
		//
		if (5 == count($functions))
		{
			$this->_plugins['resource'][$type] = array(
				array(
					array(&$functions[0], $functions[1]),
					array(&$functions[0], $functions[2]),
					array(&$functions[0], $functions[3]),
					array(&$functions[0], $functions[4])
				) ,false);
			return $this;
		}

		throw new Exception\ResourceException(
			"Malformed function-list for '{$type}' resource in "
				. __METHOD__ . '()'
			);
	}

	/**
	* Unregisters a resource
	*
	* @param string $type name of resource
	* @return self
	*/
	function unregister_resource($type) : self
	{
		unset($this->_plugins['resource'][$type]);
		return $this;
	}

    /**
     * Registers a prefilter function to apply
     * to a template before compiling
     *
     * @param callback $function
     */
    function register_prefilter($function)
    {
	$this->_plugins['prefilter'][$this->_get_filter_name($function)]
	    = array($function, null, null, false);
    }

    /**
     * Unregisters a prefilter function
     *
     * @param callback $function
     */
    function unregister_prefilter($function)
    {
	unset($this->_plugins['prefilter'][$this->_get_filter_name($function)]);
    }

    /**
     * Registers a postfilter function to apply
     * to a compiled template after compilation
     *
     * @param callback $function
     */
    function register_postfilter($function)
    {
	$this->_plugins['postfilter'][$this->_get_filter_name($function)]
	    = array($function, null, null, false);
    }

    /**
     * Unregisters a postfilter function
     *
     * @param callback $function
     */
    function unregister_postfilter($function)
    {
	unset($this->_plugins['postfilter'][$this->_get_filter_name($function)]);
    }

    /**
     * Registers an output filter function to apply
     * to a template output
     *
     * @param callback $function
     */
    function register_outputfilter($function)
    {
	$this->_plugins['outputfilter'][$this->_get_filter_name($function)]
	    = array($function, null, null, false);
    }

    /**
     * Unregisters an outputfilter function
     *
     * @param callback $function
     */
    function unregister_outputfilter($function)
    {
	unset($this->_plugins['outputfilter'][$this->_get_filter_name($function)]);
    }

    /**
     * load a filter of specified type and name
     *
     * @param string $type filter type
     * @param string $name filter name
     */
    function load_filter($type, $name)
    {
	switch ($type) {
	    case 'output':
		$_params = array('plugins' => array(array($type . 'filter', $name, null, null, false)));
		$this->_load_plugins($_params);
		break;

	    case 'pre':
	    case 'post':
		if (!isset($this->_plugins[$type . 'filter'][$name]))
		    $this->_plugins[$type . 'filter'][$name] = false;
		break;
	}
    }

    /**
     * clear all the assigned template variables.
     *
     */
    function clear_all_assign()
    {
	$this->_tpl_vars = array();
    }

    /**
     * clears compiled version of specified template resource
     *
     * @param string $tpl_file
     * @param string $compile_id
     * @param string $exp_time
     * @return boolean results of {@link \Smarty2\Kit\Files::unlink()}
     */
    function clear_compiled_tpl($tpl_file, $compile_id = null, $exp_time = null)
    {
	$smarty_compile_tpl = $this->_get_compile_path($tpl_file, $compile_id);
	return Kit\Files::unlink($smarty_compile_tpl, $exp_time);
    }

    /**
     * Checks whether requested template exists.
     *
     * @param string $tpl_file
     * @return boolean
     */
    function template_exists($tpl_file)
    {
	$_params = array('resource_name' => $tpl_file, 'quiet'=>true, 'get_source'=>false);
	return $this->_fetch_resource_info($_params);
    }

    /**
     * Returns an array containing template variables
     *
     * @param string $name
     * @param string $type
     * @return array
     */
    function &get_template_vars($name=null)
    {
	if(!isset($name)) {
	    return $this->_tpl_vars;
	} elseif(isset($this->_tpl_vars[$name])) {
	    return $this->_tpl_vars[$name];
	} else {
	    // var non-existant, return valid reference
	    $_tmp = null;
	    return $_tmp;
	}
    }

    /**
     * trigger Smarty error
     *
     * @param string $error_msg
     * @param integer $error_type
     */
    function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
	$msg = htmlentities($error_msg);
	trigger_error("Smarty error: $msg", $error_type);
    }


    /**
     * executes & displays the template results
     *
     * @param string $resource_name
     * @param string $deprecated_cache_id
     * @param string $compile_id
     */
    function display($resource_name, $deprecated_cache_id = null, $compile_id = null)
    {
	$this->fetch($resource_name, $deprecated_cache_id, $compile_id, true);
    }

    /**
     * executes & returns or displays the template results
     *
     * @param string $resource_name
     * @param string $deprecated_cache_id
     * @param string $compile_id
     * @param boolean $display
     */
    function fetch($resource_name, $deprecated_cache_id = null, $compile_id = null, $display = false)
    {
	$_smarty_old_error_level = $this->debugging ? error_reporting() : error_reporting(isset($this->error_reporting)
	       ? $this->error_reporting : error_reporting() & ~E_NOTICE);

	if ($this->debugging) {
	    // capture time for debugging info

	    $_debug_start_time = microtime(true);
	    $this->_smarty_debug_info[] = array('type'      => 'template',
						'filename'  => $resource_name,
						'depth'     => 0);
	    $_included_tpls_idx = count($this->_smarty_debug_info) - 1;
	}

	if (!isset($compile_id)) {
	    $compile_id = $this->compile_id;
	}

	$this->_compile_id = $compile_id;
	$this->_inclusion_depth = 0;

	// load filters that are marked as autoload
	if (!empty($this->autoload_filters)) {
	    foreach ($this->autoload_filters as $_filter_type => $_filters) {
		foreach ($_filters as $_filter) {
		    $this->load_filter($_filter_type, $_filter);
		}
	    }
	}

	$_smarty_compile_path = $this->_get_compile_path($resource_name);

	// if we just need to display the results, don't perform output
	// buffering - for speed
	if ($display && count($this->_plugins['outputfilter']) == 0) {
	    if ($this->_is_compiled($resource_name, $_smarty_compile_path)
		    || $this->_compile_resource($resource_name, $_smarty_compile_path))
	    {
		include($_smarty_compile_path);
	    }
	} else {
	    ob_start();
	    if ($this->_is_compiled($resource_name, $_smarty_compile_path)
		    || $this->_compile_resource($resource_name, $_smarty_compile_path))
	    {
		include($_smarty_compile_path);
	    }
	    $_smarty_results = ob_get_contents();
	    ob_end_clean();

	    foreach ((array)$this->_plugins['outputfilter'] as $_output_filter) {
		$_smarty_results = call_user_func_array($_output_filter[0], array($_smarty_results, &$this));
	    }
	}

	if ($display) {
	    if (isset($_smarty_results)) { echo $_smarty_results; }
	    if ($this->debugging) {
		// capture time for debugging info
		$this->_smarty_debug_info[$_included_tpls_idx]['exec_time'] = (microtime(true) - $_debug_start_time);
	    }
	    error_reporting($_smarty_old_error_level);
	    return;
	} else {
	    error_reporting($_smarty_old_error_level);
	    if (isset($_smarty_results)) { return $_smarty_results; }
	}
    }


	/**
	* @var Smarty2\Provider\LegacyProvider
	*/
	protected Provider\LegacyProvider $defaultPluginProvider;

	protected function getDefaultPluginProvider() : Provider\LegacyProvider
	{
		return ($this->defaultPluginProvider ??
			$this->defaultPluginProvider =
				new Provider\LegacyProvider(
					$this->plugins_dir
				));
	}

	/**
	* get filepath of requested plugin
	*
	* @param string $type
	* @param string $name
	* @return string|false
	*/
	function _get_plugin_filepath($type, $name)
	{
		return $this->getDefaultPluginProvider()->getFilepath($type, $name);
	}

	/**
	* @var Smarty2\Depot\LegacyDepot
	*/
	protected Depot\LegacyDepot $defaultCompiledDepot;

	protected function getCompiledDepot() : Depot\LegacyDepot
	{
		return ($this->defaultCompiledDepot ??
			$this->defaultCompiledDepot =
				new Depot\LegacyDepot(
					$this->compile_dir,
					$this->use_sub_dirs
				));
	}

	/**
	* test if resource needs compiling
	*
	* @param string $resource_name
	* @param string $compile_path
	* @return boolean
	*/
	function _is_compiled($resource_name, $compile_path)
	{
		/*
		* note that if the same template is included
		* multiple times within the same script, it
		* will be compled multiple times when force_compile
		* is on
		*/
		if ($this->force_compile)
		{
			return false;
		}

		if (!is_file($compile_path))
		{
			return false;
		}

		// no need to check compiled file
		if (!$this->compile_check)
		{
			return true;
		}

		// get file source and timestamp
		$_params = array('resource_name' => $resource_name, 'get_source'=>false);
		if (!$this->_fetch_resource_info($_params))
		{
			return false;
		}

		// template not expired, no recompile
		//
		return ($this->getCompiledDepot()->getTimestamp($compile_path)
			> $_params['resource_timestamp']);
	}

	/**
	* compile the template
	*
	* @param string $resource_name
	* @param string $compile_path
	* @return boolean
	*/
	function _compile_resource($resource_name, $compile_path)
	{
		$_params = array('resource_name' => $resource_name);
		if (!$this->_fetch_resource_info($_params))
		{
			return false;
		}

		$_source_content = $_params['source_content'];

		if (!$this->_compile_source(
			$resource_name,
			$_source_content,
			$_compiled_content))
		{
			return false;
		}

		return $this->getCompiledDepot()->writeCompiled(
			$compile_path, $_compiled_content
			);
	}

   /**
     * compile the given source
     *
     * @param string $resource_name
     * @param string $source_content
     * @param string $compiled_content
     * @return boolean
     */
    function _compile_source($resource_name, &$source_content, &$compiled_content)
    {
	$smarty_compiler = new $this->compiler_class;

	$smarty_compiler->force_compile     = $this->force_compile;
	$smarty_compiler->left_delimiter    = $this->left_delimiter;
	$smarty_compiler->right_delimiter   = $this->right_delimiter;
	$smarty_compiler->_version	  = $this->_version;

	$smarty_compiler->securityPolicy = $this->securityPolicy();

	$smarty_compiler->use_sub_dirs      = $this->use_sub_dirs;
	$smarty_compiler->_reg_objects      = &$this->_reg_objects;
	$smarty_compiler->_plugins	  = &$this->_plugins;
	$smarty_compiler->_tpl_vars	 = &$this->_tpl_vars;
	$smarty_compiler->default_modifiers = $this->default_modifiers;
	$smarty_compiler->compile_id	= $this->_compile_id;
	$smarty_compiler->_config	    = $this->_config;

	$_results = $smarty_compiler->_compile_file($resource_name, $source_content, $compiled_content);

	return $_results;
    }

	/**
	* Get the compile path for this resource
	*
	* @param string $resource_name
	* @param string $compile_id
	* @return string
	*/
	function _get_compile_path($resource_name, $compile_id = null)
	{
	  	return $this->getCompiledDepot()->getCompiledFilename(
			$resource_name,
			$compile_id ?? $this->compile_id
			);
	}

	/**
	* fetch the template info. Gets timestamp, and source
	* if get_source is true
	*
	* sets $source_content to the source of the template, and
	* $resource_timestamp to its timestamp
	*
	* @param string $resource_name
	* @param string $source_content
	* @param integer $resource_timestamp
	* @param boolean $get_source
	* @param boolean $quiet
	* @return boolean
	*/
	function _fetch_resource_info(&$params)
	{
		$params['get_source'] = $params['get_source'] ?? true;
		$params['quiet'] = $params['quiet'] ?? false;

		$_return = false;

		// split resource type from resrouce name
		//
		[$_resource_type, $_resource_name] =
			$this->_parse_resource_name($params['resource_name']);

		// unknown resource type ?
		//
		if (empty($this->_plugins['resource'][ $_resource_type ]))
		{
			try {
				$this->_load_resource_plugin( $_resource_type );
			}
			catch (Exception\ResourceException $e)
			{
				if ($params['quiet'])
				{
					return false;
				}

				throw $e;
			}
		}

		// call resource functions to fetch the template source and timestamp
		if ($params['get_source'])
		{
			$_source_return = isset($this->_plugins['resource'][$_resource_type])
			&& call_user_func_array(
				$this->_plugins['resource'][$_resource_type][0][0],
				array(&$_resource_name, &$params['source_content'], &$this)
				);
		} else
		{
			$_source_return = true;
		}

		$_timestamp_return = isset($this->_plugins['resource'][$_resource_type])
			&& call_user_func_array(
				$this->_plugins['resource'][$_resource_type][0][1],
				array(&$_resource_name, &$params['resource_timestamp'], &$this)
				);

		$_return = $_source_return && $_timestamp_return;

		if (!$_return)
		{
			// see if we can get a template with the default template handler
			if (!empty($this->default_template_handler_func))
			{
				if (!is_callable($this->default_template_handler_func))
				{
					$this->trigger_error(
						"default template handler function \"$this->default_template_handler_func\" doesn't exist."
						);
				} else
				{
					$_return = call_user_func_array(
						$this->default_template_handler_func,
						array(
							$_resource_type,
							$_resource_name,
							&$params['source_content'],
							&$params['resource_timestamp'], &$this)
						);
				}
			}
		}

		if (!$_return)
		{
			if (!$params['quiet'])
			{
				$this->trigger_error(
					"Unable to read resource {$_resource_type}:{$_resource_name}"
					);
			}
		}

		return $_return;
	}

	/**
	* Parse the type and name from the resource; if resource type is
	* omitted, {@link $smarty->default_resource_type} is used instead
	*
	* @param string $resource_name
	* @return array with two elements, type and name
	*/
	protected function _parse_resource_name(string $resource_name)
	{
		// split tpl_path by the first colon
		$_resource_name_parts = explode(':', $resource_name, 2);

		// no resource type given
		//
		if (count($_resource_name_parts) == 1)
		{
			return array(
				$this->default_resource_type,
				$_resource_name_parts[0]
			);
		}

		// silly windows: 1 char is not resource type, but part of filepath
		//
		if(strlen($_resource_name_parts[0]) == 1)
		{
			return array(
				$this->default_resource_type,
				$resource_name
			);
		}

		return $_resource_name_parts;
	}

	/**
	* Load a resource plugin
	*
	* @param string $type
	* @return boolean
	* @throws Smarty2\Exception\ResourceException
	*/
	protected function _load_resource_plugin(string $type)
	{
		/*
		* Resource plugins are not quite like the other ones, so they are
		* handled differently. The first element of plugin info is the array of
		* functions provided by the plugin, the second one indicates whether
		* all of them exist or not.
		*/
		if (!empty($this->_plugins['resource'][ $type ]))
		{
			return false; /* already loaded */
		}

		// load from resource.$type.php file
		//
		$_plugin_file = $this->_get_plugin_filepath('resource', $type);
		if ($_plugin_file)
		{
			/*
			* If the plugin file is found, it -must- provide the
			* properly named plugin functions.
			*/
			include_once($_plugin_file);

			/*
			* Locate functions that we require the plugin to provide.
			*/
			$_resource_ops = array('source', 'timestamp', 'secure', 'trusted');
			$_resource_funcs = array();
			foreach ($_resource_ops as $_op)
			{
				$_plugin_func = 'smarty_resource_' . $type . '_' . $_op;
				if (function_exists($_plugin_func))
				{
					$_resource_funcs[] = $_plugin_func;
					continue;
				}

				throw new Exception\ResourceException(
					"Function {$_plugin_func}() not found in {$_plugin_file}"
					);
			}

			$this->_plugins['resource'][ $type ] = array($_resource_funcs, true);
			return true;
		}

		throw new Exception\ResourceException(
			"Resource '{$type}' is not implemented"
			);
	}

    /**
     * Handle modifiers
     *
     * @param string|null $modifier_name
     * @param array|null $map_array
     * @return string result of modifiers
     */
    function _run_mod_handler()
    {
	$_args = func_get_args();
	list($_modifier_name, $_map_array) = array_splice($_args, 0, 2);
	list($_func_name, $_tpl_file, $_tpl_line) =
	    $this->_plugins['modifier'][$_modifier_name];

	$_var = $_args[0];
	foreach ($_var as $_key => $_val) {
	    $_args[0] = $_val;
	    $_var[$_key] = call_user_func_array($_func_name, $_args);
	}
	return $_var;
    }

    /**
     * trigger Smarty plugin error
     *
     * @param string $error_msg
     * @param string $tpl_file
     * @param integer $tpl_line
     * @param string $file
     * @param integer $line
     * @param integer $error_type
     */
    function _trigger_fatal_error($error_msg, $tpl_file = null, $tpl_line = null,
	    $file = null, $line = null, $error_type = E_USER_ERROR)
    {
	if(isset($file) && isset($line)) {
	    $info = ' ('.basename($file).", line $line)";
	} else {
	    $info = '';
	}
	if (isset($tpl_line) && isset($tpl_file)) {
	    $this->trigger_error('[in ' . $tpl_file . ' line ' . $tpl_line . "]: $error_msg$info", $error_type);
	} else {
	    $this->trigger_error($error_msg . $info, $error_type);
	}
    }

	/**
	* called for included templates
	*
	* @param string $_smarty_include_tpl_file
	* @param string $_smarty_include_vars
	*/
	function _smarty_include($params)
	{
		if ($this->debugging)
		{
			$debug_start_time = microtime(true);
			$this->_smarty_debug_info[] = array(
				'type' => 'template',
				'filename' => $params['smarty_include_tpl_file'],
				'depth' => ++$this->_inclusion_depth
				);

			$included_tpls_idx = count($this->_smarty_debug_info) - 1;
		}

		$this->_tpl_vars = array_merge($this->_tpl_vars, $params['smarty_include_vars']);

		$_smarty_compile_path = $this->_get_compile_path(
			$params['smarty_include_tpl_file']
			);

		if ($this->_is_compiled($params['smarty_include_tpl_file'], $_smarty_compile_path)
			|| $this->_compile_resource($params['smarty_include_tpl_file'], $_smarty_compile_path))
		{
			include($_smarty_compile_path);
		}

		$this->_inclusion_depth--;

		if ($this->debugging)
		{
			// capture time for debugging info
			$this->_smarty_debug_info[$included_tpls_idx]['exec_time'] =
				microtime(true) - $debug_start_time;
		}
	}

	/**
	* wrapper for include() retaining $this
	*
	* @param string $filename
	* @param bool $once
	* @param array $params
	* @return mixed
	*/
	function _include($filename, $once=false, $params=null)
	{
		return $once
			? include_once($filename)
			: include($filename);
	}

	/**
	* wrapper for eval() retaining $this
	* @return mixed
	*/
	function _eval($code, $params=null)
	{
		return eval($code);
	}

	/**
	* Extracts the filter name from the given callback
	*
	* @param callback $function
	* @return string
	*/
	protected function _get_filter_name(callable $function) : string
	{
		if (is_array($function))
		{
			$_class_name = (is_object($function[0]))
				? get_class($function[0])
				: $function[0] ;
			return $_class_name . '_' . $function[1];
		}

		return $function;
	}

	/**
	* Handle insert tags
	*
	* @param array $params
	* @return string
	*/
	protected function _run_insert_handler($params)
	{
		if ($this->debugging)
		{
			$_debug_start_time = microtime(true);
		}

		$_funcname = $this->_plugins['insert'][$params['args']['name']][0];
		$_content = $_funcname($params['args'], $this);

		if ($this->debugging)
		{
			$this->_smarty_debug_info[] = array(
				'type'      => 'insert',
				'filename'  => 'insert_'.$params['args']['name'],
				'depth'     => $this->_inclusion_depth,
				'exec_time' => microtime(true) - $_debug_start_time
			);
		}

		if (!empty($params['args']["assign"]))
		{
			$this->assign($params['args']["assign"], $_content);
			return '';
		}

		return $_content;
	}

	/**
	* Load requested plugins
	*
	* @param array $plugins
	*/
	protected function _load_plugins($params)
	{
		foreach ($params['plugins'] as $_plugin_info)
		{
			list($_type, $_name, $_tpl_file, $_tpl_line) = $_plugin_info;
			$_plugin = &$this->_plugins[$_type][$_name];

			/*
			* We do not load plugin more than once for each instance of Smarty.
			* The following code checks for that. The plugin can also be
			* registered dynamically at runtime, in which case template file
			* and line number will be unknown, so we fill them in.
			*
			* The final element of the info array is a flag that indicates
			* whether the dynamically registered plugin function has been
			* checked for existence yet or not.
			*/
			if (isset($_plugin))
			{
				if (empty($_plugin[3]))
				{
					if (!is_callable($_plugin[0]))
					{
						throw new Exception\PluginException(
							"[plugin] {$_type} '{$_name}' is not implemented: {$_plugin[0]}",
							$_tpl_file, $_tpl_line
							);
					}

					$_plugin[1] = $_tpl_file;
					$_plugin[2] = $_tpl_line;
					$_plugin[3] = true;
					if (!isset($_plugin[4])) $_plugin[4] = true; /* cacheable */
				}

				continue;
			}

			$_plugin_file = $this->_get_plugin_filepath($_type, $_name);

			/*
			* PHP functions as modifiers, no plugins to load
			*/
			if (!$_plugin_file && ($_type == 'modifier') && function_exists($_name))
			{
				/*
				* In case modifier falls back on using
				* PHP functions directly, we only allow
				* those specified in the security policy
				*/
				if (!$this->securityPolicy()->isModifierAllowed($_name))
				{
					throw new Exception\PluginException(
						"(secure mode) modifier '{$_name}' is not allowed",
						$_tpl_file, $_tpl_line
						);
				}

				$_plugin_func = $_name;
				$this->_plugins[$_type][$_name] =
					array($_plugin_func, $_tpl_file, $_tpl_line, true, true);
				continue;
			}

			if (!$_plugin_file)
			{
				throw new Exception\PluginException(
					"[plugin] {$_type} '{$_name}' is not implemented",
					$_tpl_file, $_tpl_line
					);
			}

			// included files rely on $smarty being present
			// there in the local variable scope
			//
			$smarty =& $this;
			include_once $_plugin_file;

			/*
			* If plugin file is found, it -must- provide the properly named
			* plugin function. In case it doesn't, simply output the error and
			* do not fall back on any other method.
			*/
			$_plugin_func = 'smarty_' . $_type . '_' . $_name;
			if (!function_exists($_plugin_func))
			{
				throw new Exception\PluginException(
					"[plugin] function {$_plugin_func}() not found in {$_plugin_file}",
					$_tpl_file, $_tpl_line
					);
			}

			$this->_plugins[$_type][$_name] =
				array($_plugin_func, $_tpl_file, $_tpl_line, true, true);
		}
	}

	/**
	* @var Smarty2\Security
	*/
	protected SmartySecurity $securityPolicy;

	protected function securityPolicy() : SmartySecurity
	{
		return ($this->securityPolicy ??
			$this->securityPolicy = $this->security
				? new SmartySecurity(
					$this->security_settings['IF_FUNCS'],
					$this->security_settings['MODIFIER_FUNCS'],
					$this->security_settings['ALLOW_CONSTANTS'],
					$this->security_settings['ALLOW_SUPER_GLOBALS'],
				)
				: new SmartySecurity()
			);
	}
}
