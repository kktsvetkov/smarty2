<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Handle insert tags
 *
 * @param array $args
 * @return string
 */
function smarty_core_run_insert_handler($params, &$smarty)
{

    if ($smarty->debugging) {
        $_params = array();
        $_debug_start_time = microtime(true);
    }

    if ($smarty->caching) {
        $_arg_string = serialize($params['args']);
        $_name = $params['args']['name'];
        if (!isset($smarty->_cache_info['insert_tags'][$_name])) {
            $smarty->_cache_info['insert_tags'][$_name] = array('insert',
                                                             $_name,
                                                             $smarty->_plugins['insert'][$_name][1],
                                                             $smarty->_plugins['insert'][$_name][2],
                                                             !empty($params['args']['script']) ? true : false);
        }
        return $smarty->_smarty_md5."{insert_cache $_arg_string}".$smarty->_smarty_md5;
    } else {
        if (isset($params['args']['script'])) {
            $_params = array('resource_name' => $smarty->_dequote($params['args']['script']));

            if(!Smarty_Core::get_php_resource($_params, $smarty)) {
                return false;
            }

            if ($_params['resource_type'] == 'file') {
                $smarty->_include($_params['php_resource'], true);
            } else {
                $smarty->_eval($_params['php_resource']);
            }
            unset($params['args']['script']);
        }

        $_funcname = $smarty->_plugins['insert'][$params['args']['name']][0];
        $_content = $_funcname($params['args'], $smarty);
        if ($smarty->debugging) {
            $_params = array();

            $smarty->_smarty_debug_info[] = array('type'      => 'insert',
                                                'filename'  => 'insert_'.$params['args']['name'],
                                                'depth'     => $smarty->_inclusion_depth,
                                                'exec_time' => microtime(true) - $_debug_start_time);
        }

        if (!empty($params['args']["assign"])) {
            $smarty->assign($params['args']["assign"], $_content);
        } else {
            return $_content;
        }
    }
}
