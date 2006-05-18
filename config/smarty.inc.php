<?php
require_once('Smarty/libs/Smarty.class.php');
require_once('S2Base/S2Base.web.php');

/**
 * php log setting
 */
error_reporting(E_ERROR);
error_reporting(E_WARNING);
error_reporting(E_ALL);
ini_set('display_errors','On');
ini_set('log_errors','On');
ini_set('error_log',S2BASE_PHP5_VAR_DIR . '/logs');

/**
 * session path setting
 */
session_save_path(S2BASE_PHP5_VAR_DIR . '/session');

/**
 * global lyaout setting
 */
//define('S2BASE_PHP5_LAYOUT','file:' . S2BASE_PHP5_ROOT . '/app/commons/view/layout.tpl');

/**
 * cache switch
 */
define('S2BASE_PHP5_CACHE_ON',false);

/**
 * Smarty config
 *     S2Base_SmartyController::$config['property name'] = property value
 */
S2Base_SmartyController::$config['compile_dir'] = S2BASE_PHP5_VAR_DIR . '/smarty/template_c';
S2Base_SmartyController::$config['config_dir']  = S2BASE_PHP5_VAR_DIR . '/smarty/config';
S2Base_SmartyController::$config['cache_dir']   = S2BASE_PHP5_VAR_DIR . '/smarty/cache';
S2Base_SmartyController::$config['caching']     = 0;
?>
