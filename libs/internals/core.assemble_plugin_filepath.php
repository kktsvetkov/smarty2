<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * assemble filepath of requested plugin
 *
 * @param string $type
 * @param string $name
 * @return string|false
 */
function smarty_core_assemble_plugin_filepath($params, &$smarty)
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
