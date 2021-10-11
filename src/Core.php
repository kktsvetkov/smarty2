<?php

namespace Smarty2;

/**
* Collection of Smarty core operations
*
* Methods here are imported from the code inside libs/internals
*
* @package Smarty2
*/
class Core
{
	/**
	 * Load requested plugins
	 *
	 * @param array $plugins
	 */
	static function load_plugins($params, &$smarty)
	{
	    foreach ($params['plugins'] as $_plugin_info) {
		list($_type, $_name, $_tpl_file, $_tpl_line, $_delayed_loading) = $_plugin_info;
		$_plugin = &$smarty->_plugins[$_type][$_name];

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
		if (isset($_plugin)) {
		    if (empty($_plugin[3])) {
			if (!is_callable($_plugin[0])) {
			    $smarty->_trigger_fatal_error("[plugin] $_type '$_name' is not implemented", $_tpl_file, $_tpl_line, __FILE__, __LINE__);
			} else {
			    $_plugin[1] = $_tpl_file;
			    $_plugin[2] = $_tpl_line;
			    $_plugin[3] = true;
			    if (!isset($_plugin[4])) $_plugin[4] = true; /* cacheable */
			}
		    }
		    continue;
		} else if ($_type == 'insert') {
		    /*
		     * For backwards compatibility, we check for insert functions in
		     * the symbol table before trying to load them as a plugin.
		     */
		    $_plugin_func = 'insert_' . $_name;
		    if (function_exists($_plugin_func)) {
			$_plugin = array($_plugin_func, $_tpl_file, $_tpl_line, true, false);
			continue;
		    }
		}

		$_plugin_file = $smarty->_get_plugin_filepath($_type, $_name);

		if (! $_found = ($_plugin_file != false)) {
		    $_message = "could not load plugin file '$_type.$_name.php'\n";
		}

		/*
		 * If plugin file is found, it -must- provide the properly named
		 * plugin function. In case it doesn't, simply output the error and
		 * do not fall back on any other method.
		 */
		if ($_found) {
		    include_once $_plugin_file;

		    $_plugin_func = 'smarty_' . $_type . '_' . $_name;
		    if (!function_exists($_plugin_func)) {
			$smarty->_trigger_fatal_error("[plugin] function $_plugin_func() not found in $_plugin_file", $_tpl_file, $_tpl_line, __FILE__, __LINE__);
			continue;
		    }
		}
		/*
		 * In case of insert plugins, their code may be loaded later via
		 * 'script' attribute.
		 */
		else if ($_type == 'insert' && $_delayed_loading) {
		    $_plugin_func = 'smarty_' . $_type . '_' . $_name;
		    $_found = true;
		}

		/*
		 * Plugin specific processing and error checking.
		 */
		if (!$_found) {
		    if ($_type == 'modifier') {
			/*
			 * In case modifier falls back on using PHP functions
			 * directly, we only allow those specified in the security
			 * context.
			 */
			if ($smarty->security && !in_array($_name, $smarty->security_settings['MODIFIER_FUNCS'])) {
			    $_message = "(secure mode) modifier '$_name' is not allowed";
			} else {
			    if (!function_exists($_name)) {
				$_message = "modifier '$_name' is not implemented";
			    } else {
				$_plugin_func = $_name;
				$_found = true;
			    }
			}
		    } else if ($_type == 'function') {
			/*
			 * This is a catch-all situation.
			 */
			$_message = "unknown tag - '$_name'";
		    }
		}

		if ($_found) {
		    $smarty->_plugins[$_type][$_name] = array($_plugin_func, $_tpl_file, $_tpl_line, true, true);
		} else {
		    // output error
		    $smarty->_trigger_fatal_error('[plugin] ' . $_message, $_tpl_file, $_tpl_line, __FILE__, __LINE__);
		}
	    }
	}

	/**
	* Load a resource plugin
	*
	* @param string $type
	* @param Smarty $smarty
	*/
	static function load_resource_plugin($type, &$smarty)
	{
	    /*
	     * Resource plugins are not quite like the other ones, so they are
	     * handled differently. The first element of plugin info is the array of
	     * functions provided by the plugin, the second one indicates whether
	     * all of them exist or not.
	     */

	    $_plugin = &$smarty->_plugins['resource'][ $type ];

	    if (isset($_plugin))
	    {
		if (!$_plugin[1] && count($_plugin[0]))
		{
		    $_plugin[1] = true;
		    foreach ($_plugin[0] as $_plugin_func)
		    {
			if (!is_callable($_plugin_func))
			{
			    $_plugin[1] = false;
			    break;
			}
		    }
		}

		if (!$_plugin[1])
		{
		    $smarty->_trigger_fatal_error(
			    "[plugin] resource '{$type}' is not implemented", null, null,
			    __FILE__, __LINE__);
		}

		return;
	    }

	    $_plugin_file = $smarty->_get_plugin_filepath('resource', $type);
	    $_found = ($_plugin_file != false);

	    if ($_found) {
		 /*
		 * If the plugin file is found, it -must- provide the properly named
		 * plugin functions.
		 */
		include_once($_plugin_file);

		/*
		 * Locate functions that we require the plugin to provide.
		 */
		$_resource_ops = array('source', 'timestamp', 'secure', 'trusted');
		$_resource_funcs = array();
		foreach ($_resource_ops as $_op) {
		    $_plugin_func = 'smarty_resource_' . $type . '_' . $_op;
		    if (!function_exists($_plugin_func))
		    {
			$smarty->_trigger_fatal_error(
				"[plugin] function {$_plugin_func}() not found in {$_plugin_file}",
				null, null, __FILE__, __LINE__);
			return;
		    } else {
			$_resource_funcs[] = $_plugin_func;
		    }
		}

		$smarty->_plugins['resource'][ $type ] = array($_resource_funcs, true);
	    }
	}

	/**
	 * Handle insert tags
	 *
	 * @param array $args
	 * @return string
	 */
	static function run_insert_handler($params, &$smarty)
	{
		if ($smarty->debugging)
		{
			$_debug_start_time = microtime(true);
		}

		$_funcname = $smarty->_plugins['insert'][$params['args']['name']][0];
		$_content = $_funcname($params['args'], $smarty);

		if ($smarty->debugging)
		{
			$smarty->_smarty_debug_info[] = array(
				'type'      => 'insert',
				'filename'  => 'insert_'.$params['args']['name'],
				'depth'     => $smarty->_inclusion_depth,
				'exec_time' => microtime(true) - $_debug_start_time
			);
		}

		if (!empty($params['args']["assign"]))
		{
			$smarty->assign($params['args']["assign"], $_content);
			return '';
		}

		return $_content;
	}

	/**
	* Always returns a string for what is inside $subject
	*
	* @param mixed $subject
	* @return string
	*/
	static function to_string($subject) : string
	{
		// return only objects that can be made into strings
		//
		if (is_object($subject))
		{
			return is_callable([$subject, '__toString'])
				? $subject->__toString()
				: '';
		}

		// there isn't a good way to cast an array as string
		//
		if (is_array($subject))
		{
			return 'Array';
		}

		return (string) $subject;
	}

	/**
	* write out a file to disk
	*
	* @param string $filename
	* @param string $contents
	* @return boolean
	*/
	static function write_file(string $filename, string $contents, &$smarty)
	{
		$folder = dirname($filename);
		if (!is_dir($folder))
		{
			if (false === mkdir($folder, $smarty->_dir_perms, true))
			{
				$smarty->trigger_error("problem creating folder '{$folder}'");
				return false;
			}
		}

		$saved = file_put_contents($filename, $contents, LOCK_EX);

		if (false === $saved)
		{
			$smarty->trigger_error("problem writing file '{$filename}'");
			return false;
		}

		chmod($filename, $smarty->_file_perms);
		return true;
	}

	/**
	* unlink a file, possibly using expiration time
	*
	* @param string $resource
	* @param integer $exp_time
	*/
	static function unlink(string $resource, $exp_time = null)
	{
		if (!is_file($resource))
		{
			return false;
		}

		if (!empty($exp_time))
		{
			return (time() - filemtime($resource) >= $exp_time)
				? unlink($resource)
				: false;
		}

		return unlink($resource);
	}
}
