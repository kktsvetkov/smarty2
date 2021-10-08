<?php

/**
* Collection of Smarty core operations
*
* Methods here are imported from the code inside libs/internals
*
* @package Smarty2
*/
class Smarty_Core
{
        /**
         * assemble filepath of requested plugin
         *
         * @param string $type
         * @param string $name
         * @return string|false
         */
        function assemble_plugin_filepath($params, &$smarty)
        {
            $_plugin_filename = $params['type'] . '.' . $params['name'] . '.php';
            if (isset($smarty->_filepaths_cache[$_plugin_filename])) {
                return $smarty->_filepaths_cache[$_plugin_filename];
            }
            $_return = false;

            foreach ((array)$smarty->plugins_dir as $_plugin_dir) {

                $_plugin_filepath = $_plugin_dir . DIRECTORY_SEPARATOR . $_plugin_filename;

                // see if path is relative
                if (!preg_match("/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/", $_plugin_dir)) {
                    $_relative_paths[] = $_plugin_dir;
                    // relative path, see if it is in the SMARTY_DIR
                    if (@is_readable(SMARTY_DIR . $_plugin_filepath)) {
                        $_return = SMARTY_DIR . $_plugin_filepath;
                        break;
                    }
                }
                // try relative to cwd (or absolute)
                if (@is_readable($_plugin_filepath)) {
                    $_return = $_plugin_filepath;
                    break;
                }
            }

            $smarty->_filepaths_cache[$_plugin_filename] = $_return;
            return $_return;
        }

        /**
         * create full directory structure
         * @param string $dir
         */
        function create_dir_structure($params, &$smarty)
        {
            if (!is_dir($params['dir'])) {

                if (DIRECTORY_SEPARATOR=='/') {
                    /* unix-style paths */
                    $_dir = $params['dir'];
                    $_dir_parts = preg_split('!/+!', $_dir, -1, PREG_SPLIT_NO_EMPTY);
                    $_new_dir = (substr($_dir, 0, 1)=='/') ? '/' : getcwd().'/';

                } else {
                    /* other-style paths */
                    $_dir = str_replace('\\','/', $params['dir']);
                    $_dir_parts = preg_split('!/+!', $_dir, -1, PREG_SPLIT_NO_EMPTY);
                    if (preg_match('!^((//)|([a-zA-Z]:/))!', $_dir, $_root_dir)) {
                        /* leading "//" for network volume, or "[letter]:/" for full path */
                        $_new_dir = $_root_dir[1];
                        /* remove drive-letter from _dir_parts */
                        if (isset($_root_dir[3])) array_shift($_dir_parts);

                    } else {
                        $_new_dir = str_replace('\\', '/', getcwd()).'/';

                    }
                }

                /* all paths use "/" only from here */
                foreach ($_dir_parts as $_dir_part) {
                    $_new_dir .= $_dir_part;

                    if ($_use_open_basedir) {
                        // do not attempt to test or make directories outside of open_basedir
                        $_make_new_dir = false;
                        foreach ($_open_basedirs as $_open_basedir) {
                            if (substr($_new_dir, 0, strlen($_open_basedir)) == $_open_basedir) {
                                $_make_new_dir = true;
                                break;
                            }
                        }
                    } else {
                        $_make_new_dir = true;
                    }

                    if ($_make_new_dir && !file_exists($_new_dir) && !@mkdir($_new_dir, $smarty->_dir_perms) && !is_dir($_new_dir)) {
                        $smarty->trigger_error("problem creating directory '" . $_new_dir . "'");
                        return false;
                    }
                    $_new_dir .= '/';
                }
            }
        }


        /**
         * Retrieves PHP script resource
         *
         * sets $php_resource to the returned resource
         * @param string $resource
         * @param string $resource_type
         * @param  $php_resource
         * @return boolean
         */
        function get_php_resource(&$params, &$smarty)
        {
            $params['resource_base_path'] = array();
            $smarty->_parse_resource_name($params, $smarty);

            /*
             * Find out if the resource exists.
             */

            if ($params['resource_type'] == 'file') {
                $_readable = false;
                if(file_exists($params['resource_name']) && is_readable($params['resource_name'])) {
                    $_readable = true;
                }
            } else if ($params['resource_type'] != 'file') {
                $_template_source = null;
                $_readable = is_callable($smarty->_plugins['resource'][$params['resource_type']][0][0])
                    && call_user_func_array($smarty->_plugins['resource'][$params['resource_type']][0][0],
                                            array($params['resource_name'], &$_template_source, &$smarty));
            }

            /*
             * Set the error function, depending on which class calls us.
             */
            if (method_exists($smarty, '_syntax_error')) {
                $_error_funcc = '_syntax_error';
            } else {
                $_error_funcc = 'trigger_error';
            }

            if (!$_readable) {
                $smarty->$_error_funcc($params['resource_type'] . ':' . $params['resource_name'] . ' is not readable');
                return false;
            }

            if ($params['resource_type'] == 'file') {
                $params['php_resource'] = $params['resource_name'];
            } else {
                $params['php_resource'] = $_template_source;
            }
            return true;
        }


        /**
         * Load requested plugins
         *
         * @param array $plugins
         */
        function load_plugins($params, &$smarty)
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
         * called for included php files within templates
         *
         * @param string $smarty_file
         * @param string $smarty_assign variable to assign the included template's
         *               output into
         * @param boolean $smarty_once uses include_once if this is true
         * @param array $smarty_include_vars associative array of vars from
         *              {include file="blah" var=$var}
         */
        function smarty_include_php($params, &$smarty)
        {
            $_params = array('resource_name' => $params['smarty_file']);

            Smarty_Core::get_php_resource($_params, $smarty);
            $_smarty_resource_type = $_params['resource_type'];
            $_smarty_php_resource = $_params['php_resource'];

            if (!empty($params['smarty_assign'])) {
                ob_start();
                if ($_smarty_resource_type == 'file') {
                    $smarty->_include($_smarty_php_resource, $params['smarty_once'], $params['smarty_include_vars']);
                } else {
                    $smarty->_eval($_smarty_php_resource, $params['smarty_include_vars']);
                }
                $smarty->assign($params['smarty_assign'], ob_get_contents());
                ob_end_clean();
            } else {
                if ($_smarty_resource_type == 'file') {
                    $smarty->_include($_smarty_php_resource, $params['smarty_once'], $params['smarty_include_vars']);
                } else {
                    $smarty->_eval($_smarty_php_resource, $params['smarty_include_vars']);
                }
            }
        }


        /**
         * write out a file to disk
         *
         * @param string $filename
         * @param string $contents
         * @param boolean $create_dirs
         * @return boolean
         */
        function write_file($params, &$smarty)
        {
            $_dirname = dirname($params['filename']);

            if ($params['create_dirs']) {
                $_params = array('dir' => $_dirname);
                self::create_dir_structure($_params, $smarty);
            }

            // write to tmp file, then rename it to avoid file locking race condition
            $_tmp_file = tempnam($_dirname, 'wrt');

            if (!($fd = @fopen($_tmp_file, 'wb'))) {
                $_tmp_file = $_dirname . DIRECTORY_SEPARATOR . uniqid('wrt');
                if (!($fd = @fopen($_tmp_file, 'wb'))) {
                    $smarty->trigger_error("problem writing temporary file '$_tmp_file'");
                    return false;
                }
            }

            fwrite($fd, $params['contents']);
            fclose($fd);

            if (DIRECTORY_SEPARATOR == '\\' || !@rename($_tmp_file, $params['filename'])) {
                // On platforms and filesystems that cannot overwrite with rename()
                // delete the file before renaming it -- because windows always suffers
                // this, it is short-circuited to avoid the initial rename() attempt
                @unlink($params['filename']);
                @rename($_tmp_file, $params['filename']);
            }
            @chmod($params['filename'], $smarty->_file_perms);

            return true;
        }

}
