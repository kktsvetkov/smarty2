<?php

namespace Smarty2;

use Smarty2\Exception\FilepathException;

/**
* Smarty2 - the PHP template engine
*
* @package Smarty2
*/
class Engine
{
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
    public bool $security       =   false;

    /**
     * These are the security settings for Smarty. They are used only when
     * {@link $security} is enabled.
     *
     * @var array
     */
    public array $security_settings  = array(
	    'IF_FUNCS'	=> array('array', 'list',
				       'isset', 'empty',
				       'count', 'sizeof',
				       'in_array', 'is_array',
				       'true', 'false', 'null'),
	    'MODIFIER_FUNCS'  => array('count'),
	    'ALLOW_CONSTANTS'  => false,
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
     * loaded configuration settings
     *
     * @var array
     */
    var $_config = array();

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
     * default file permissions
     *
     * @var integer
     */
    var $_file_perms	   = 0644;

    /**
     * default dir permissions
     *
     * @var integer
     */
    var $_dir_perms	       = 0771;

    /**
     * registered objects
     *
     * @var array
     */
    var $_reg_objects	   = array();

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
     * Registers object to be used in templates
     *
     * @param string $object name of template object
     * @param object &$object_impl the referenced PHP object to register
     * @param null|array $allowed list of allowed methods (empty = all)
     * @param boolean $smarty_args smarty argument format, else traditional
     * @param null|array $block_functs list of methods that are block format
     */
    function register_object($object, &$object_impl, $allowed = array(), $smarty_args = true, $block_methods = array())
    {
	settype($allowed, 'array');
	settype($smarty_args, 'boolean');
	$this->_reg_objects[$object] =
	    array(&$object_impl, $allowed, $smarty_args, $block_methods);
    }

    /**
     * Unregisters object
     *
     * @param string $object name of template object
     */
    function unregister_object($object)
    {
	unset($this->_reg_objects[$object]);
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
     */
    function register_resource($type, $functions)
    {
	if (count($functions)==4) {
	    $this->_plugins['resource'][$type] =
		array($functions, false);

	} elseif (count($functions)==5) {
	    $this->_plugins['resource'][$type] =
		array(array(array(&$functions[0], $functions[1])
			    ,array(&$functions[0], $functions[2])
			    ,array(&$functions[0], $functions[3])
			    ,array(&$functions[0], $functions[4]))
		      ,false);

	} else {
	    $this->trigger_error("malformed function-list for '$type' in register_resource");

	}
    }

    /**
     * Unregisters a resource
     *
     * @param string $type name of resource
     */
    function unregister_resource($type)
    {
	unset($this->_plugins['resource'][$type]);
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
		\Smarty2\Core::load_plugins($_params, $this);
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
     * @return boolean results of {@link \Smarty2\Core::unlink()}
     */
    function clear_compiled_tpl($tpl_file, $compile_id = null, $exp_time = null)
    {
	$smarty_compile_tpl = $this->_get_compile_path($tpl_file, $compile_id);
	return \Smarty2\Core::unlink($smarty_compile_tpl, $exp_time);
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
     * Returns an array containing config variables
     *
     * @param string $name
     * @return array
     */
    function get_config_vars($name=null)
    {
	    if (null === $name)
	    {
		    return $this->_config;
	    }

	    return $this->_config[$name] ?? [];
    }

	/**
	* Introduces values as config vars
	*
	* @param array|string $name the config var name
	* @param mixed $value the value to assign
	* @return self
	*/
	function set_config_vars($name, $value = null)
	{
		if (is_array($name))
		{
			foreach ($name as $key => $val)
			{
				if ($key != '')
				{
					$this->_config[$key] = $val;
				}
			}
		} else
		if ($name != '')
		{
			$this->_config[$name] = $value;
		}

		return $this;
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
     * return a reference to a registered object
     *
     * @param string $name
     * @return object
     */
    function &get_registered_object($name) {
	if (!isset($this->_reg_objects[$name]))
	$this->_trigger_fatal_error("'$name' is not a registered object");

	if (!is_object($this->_reg_objects[$name][0]))
	$this->_trigger_fatal_error("registered '$name' is not an object");

	return $this->_reg_objects[$name][0];
    }

    /**
     * clear configuration values
     *
     * @param string $var
     */
    function clear_config($var = null)
    {
	if(null === $var)
	{
	    // clear all values
	    $this->_config = array();
	} else {
	    unset($this->_config[$var]);
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
		if ($_params['resource_timestamp'] <= filemtime($compile_path))
		{
			return true;
		}

		return false;
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

		if (!isset($this->compiled_dir_inspected))
		{
			$this->compiled_dir_inspected = true;
			$this->inspect_compiled_dir($this->compile_dir);
		}

		return \Smarty2\Core::write_file(
			$compile_path,
			$_compiled_content,
			$this
			);
	}

	/**
	* @var boolean flag whether {$smarty->compiled_dir} was inspected
	* @see Smarty2\Engine::inspect_compiled_dir()
	*/
	protected bool $compiled_dir_inspected;

	/**
	* Inspect that the {$smarty->compiled_dir} exists and is writable
	*
	* @param string $compiled_dir
	* @throws Smarty2\Exception\FilepathException
	*/
	protected function inspect_compiled_dir(string $compiled_dir)
	{
		if (!is_dir($compiled_dir))
		{
			if (is_file($compiled_dir))
			{
				throw new FilepathException(
					"Compiled templates folder '{$compiled_dir}' is not a folder;",
					$compiled_dir
					);
			}

			if (false === mkdir($compiled_dir, $this->_dir_perms, true))
			{
				throw new FilepathException(
					"Compiled templates folder '{$compiled_dir}' can not be created;",
					$compiled_dir
					);
			}
		}

		if (!is_writable($compiled_dir))
		{
			throw new FilepathException(
				"Compiled templates folder '{$compiled_dir}' is not writable",
				$compiled_dir
				);
		}
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

	$smarty_compiler->template_dir      = $this->template_dir;
	$smarty_compiler->compile_dir       = $this->compile_dir;
	$smarty_compiler->plugins_dir       = $this->plugins_dir;

	$smarty_compiler->force_compile     = $this->force_compile;
	$smarty_compiler->left_delimiter    = $this->left_delimiter;
	$smarty_compiler->right_delimiter   = $this->right_delimiter;
	$smarty_compiler->_version	  = $this->_version;
	$smarty_compiler->security	  = $this->security;

	$smarty_compiler->security_settings = $this->security_settings;

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
     * @return string results of {@link _get_auto_filename()}
     */
    function _get_compile_path($resource_name, $compile_id = null)
    {
	return $this->_get_auto_filename(
		$this->compile_dir,
		$resource_name,
		$compile_id ?? $this->compile_id) . '.php';
    }

    /**
     * fetch the template info. Gets timestamp, and source
     * if get_source is true
     *
     * sets $source_content to the source of the template, and
     * $resource_timestamp to its time stamp
     * @param string $resource_name
     * @param string $source_content
     * @param integer $resource_timestamp
     * @param boolean $get_source
     * @param boolean $quiet
     * @return boolean
     */

    function _fetch_resource_info(&$params)
    {
	if(!isset($params['get_source'])) { $params['get_source'] = true; }
	if(!isset($params['quiet'])) { $params['quiet'] = false; }

	$_return = false;
	$_params = array('resource_name' => $params['resource_name']) ;
	if (isset($params['resource_base_path']))
	    $_params['resource_base_path'] = $params['resource_base_path'];
	else
	    $_params['resource_base_path'] = $this->template_dir;

	if ($this->_parse_resource_name($_params)) {
	    $_resource_type = $_params['resource_type'];
	    $_resource_name = $_params['resource_name'];
	    switch ($_resource_type) {
		case 'file':
		    $_return = is_file($_resource_name) && is_readable($_resource_name);
		    if ($_return)
		    {
			if ($params['get_source'])
			{
			    $params['source_content'] = file_get_contents($_resource_name);
			}
			$params['resource_timestamp'] = filemtime($_resource_name);
		    }
		    break;

		default:
		    // call resource functions to fetch the template source and timestamp
		    if ($params['get_source']) {
			$_source_return = isset($this->_plugins['resource'][$_resource_type]) &&
			    call_user_func_array($this->_plugins['resource'][$_resource_type][0][0],
						 array($_resource_name, &$params['source_content'], &$this));
		    } else {
			$_source_return = true;
		    }

		    $_timestamp_return = isset($this->_plugins['resource'][$_resource_type]) &&
			call_user_func_array($this->_plugins['resource'][$_resource_type][0][1],
					     array($_resource_name, &$params['resource_timestamp'], &$this));

		    $_return = $_source_return && $_timestamp_return;
		    break;
	    }
	}

	if (!$_return) {
	    // see if we can get a template with the default template handler
	    if (!empty($this->default_template_handler_func)) {
		if (!is_callable($this->default_template_handler_func)) {
		    $this->trigger_error("default template handler function \"$this->default_template_handler_func\" doesn't exist.");
		} else {
		    $_return = call_user_func_array(
			$this->default_template_handler_func,
			array($_params['resource_type'], $_params['resource_name'], &$params['source_content'], &$params['resource_timestamp'], &$this));
		}
	    }
	}

	if (!$_return) {
	    if (!$params['quiet']) {
		$this->trigger_error('unable to read resource: "' . $params['resource_name'] . '"');
	    }
	}
	return $_return;
    }


    /**
     * parse out the type and name from the resource
     *
     * @param string $resource_base_path
     * @param string $resource_name
     * @param string $resource_type
     * @param string $resource_name
     * @return boolean
     */
    function _parse_resource_name(&$params)
    {

	// split tpl_path by the first colon
	$_resource_name_parts = explode(':', $params['resource_name'], 2);

	if (count($_resource_name_parts) == 1) {
	    // no resource type given
	    $params['resource_type'] = $this->default_resource_type;
	    $params['resource_name'] = $_resource_name_parts[0];
	} else {
	    if(strlen($_resource_name_parts[0]) == 1) {
		// 1 char is not resource type, but part of filepath
		$params['resource_type'] = $this->default_resource_type;
		$params['resource_name'] = $params['resource_name'];
	    } else {
		$params['resource_type'] = $_resource_name_parts[0];
		$params['resource_name'] = $_resource_name_parts[1];
	    }
	}

	if ($params['resource_type'] == 'file') {
	    if (!preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $params['resource_name'])) {
		// relative pathname to $params['resource_base_path']
		// use the first directory where the file is found
		foreach ((array)$params['resource_base_path'] as $_curr_path) {
		    $_fullpath = $_curr_path . DIRECTORY_SEPARATOR . $params['resource_name'];
		    if (is_file($_fullpath))
		    {
			$params['resource_name'] = $_fullpath;
			return true;
		    }
		}
		return false;
	    } else {
		/* absolute path */
		return is_file($params['resource_name']);
	    }
	} else
	if (empty($this->_plugins['resource'][$params['resource_type']]))
	{
	    $_params = array('type' => $params['resource_type']);
	    \Smarty2\Core::load_resource_plugin($_params, $this);
	}

	return true;
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
     * Remove starting and ending quotes from the string
     *
     * @param string $string
     * @return string
     */
    function _dequote($string)
    {
	if ((substr($string, 0, 1) == "'" || substr($string, 0, 1) == '"') &&
	    substr($string, -1) == substr($string, 0, 1))
	    return substr($string, 1, -1);
	else
	    return $string;
    }

    /**
     * get a concrete filename for automagically created content
     *
     * @param string $auto_base
     * @param string $auto_source
     * @param string $auto_id
     * @return string
     */
    function _get_auto_filename($auto_base, $auto_source = null, $auto_id = null)
    {
	$_compile_dir_sep =  $this->use_sub_dirs ? DIRECTORY_SEPARATOR : '^';
	$_return = $auto_base . DIRECTORY_SEPARATOR;

	if(isset($auto_id)) {
	    // make auto_id safe for directory names
	    $auto_id = str_replace('%7C',$_compile_dir_sep,(urlencode($auto_id)));
	    // split into separate directories
	    $_return .= $auto_id . $_compile_dir_sep;
	}

	if(isset($auto_source)) {
	    // make source name safe for filename
	    $_filename = urlencode(basename($auto_source));
	    $_crc32 = sprintf('%08X', crc32($auto_source));
	    // prepend %% to avoid name conflicts with
	    // with $params['auto_id'] names
	    $_crc32 = substr($_crc32, 0, 2) . $_compile_dir_sep .
		      substr($_crc32, 0, 3) . $_compile_dir_sep . $_crc32;
	    $_return .= '%%' . $_crc32 . '%%' . $_filename;
	}

	return $_return;
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
	if ($this->debugging) {
	    $debug_start_time = microtime(true);
	    $this->_smarty_debug_info[] = array('type'      => 'template',
			'filename'  => $params['smarty_include_tpl_file'],
			'depth'     => ++$this->_inclusion_depth);
	    $included_tpls_idx = count($this->_smarty_debug_info) - 1;
	}

	$this->_tpl_vars = array_merge($this->_tpl_vars, $params['smarty_include_vars']);

	$_smarty_compile_path = $this->_get_compile_path($params['smarty_include_tpl_file']);

	if ($this->_is_compiled($params['smarty_include_tpl_file'], $_smarty_compile_path)
	    || $this->_compile_resource($params['smarty_include_tpl_file'], $_smarty_compile_path))
	{
	    include($_smarty_compile_path);
	}

	$this->_inclusion_depth--;

	if ($this->debugging) {
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

	/**#@+
	* Deprecated Methods Section
	*/

	/**
	* {@deprecated}
	*/
	function clear_cache()
	{
		return false;
	}

	/**
	* {@deprecated}
	*/
	function clear_all_cache()
	{
		return false;
	}

	/**
	* {@deprecated}
	*/
	function is_cached()
	{
		return false;
	}

	/**
	* {@deprecated}
	*/
	function _get_auto_id()
	{
		return null;
	}

	/**
	* {@deprecated}
	*/
	function _read_file($filename)
	{
		if (!is_file($filename))
		{
			return '';
		}

		return file_get_contents($filename);
	}

	/**
	* {@deprecated}
	*/
	function &_smarty_cache_attrs()
	{
		static $dummy = [];
		return $dummy;
	}

	/**
	* {@deprecated}
	* @internal use Smarty2\Core::unlink() instead
	*/
	function _unlink($resource, $exp_time = null)
	{
		return Core::unlink($resource, $exp_time);
	}

	/**#@+
	* END Deprecated Methods Section
	*/
}
