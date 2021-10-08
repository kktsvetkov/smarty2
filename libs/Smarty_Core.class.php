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
