<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Retrieves PHP script resource
 *
 * sets $php_resource to the returned resource
 * @param string $resource
 * @param string $resource_type
 * @param  $php_resource
 * @return boolean
 */

function smarty_core_get_php_resource(&$params, &$smarty)
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
