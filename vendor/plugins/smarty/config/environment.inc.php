<?php
/**
 * php log setting
 */
//error_reporting(E_ERROR);
//error_reporting(E_WARNING);
error_reporting(E_ALL);
ini_set('display_errors','On');
ini_set('log_errors','On');
ini_set('error_log',S2BASE_PHP5_VAR_DIR . '/logs/php.log');

/**
 * Library setting
 */
require_once('Smarty/libs/Smarty.class.php');
require_once('S2Base/S2Base.web.php');

/**
 * definition
 */
define('S2BASE_PHP5_SMARTY_TPL_SUFFIX','.tpl'); 

/**
 * Directory setting
 */
define('S2BASE_PHP5_PLUGIN_SMARTY',S2BASE_PHP5_ROOT . '/vendor/plugins/smarty');

/**
 * session path setting
 */
if (!defined('S2BASE_PHP5_SESSION_DIR')) {
    define('S2BASE_PHP5_SESSION_DIR', S2BASE_PHP5_VAR_DIR . '/session');
}
session_save_path(S2BASE_PHP5_SESSION_DIR);

/**
 * global lyaout setting
 */
//define('S2BASE_PHP5_LAYOUT','file:' . S2BASE_PHP5_ROOT . '/app/commons/view/layout.tpl');

/**
 * Smarty config
 *     S2Base_SmartyController::$config['property name'] = property value
 */
S2Base_SmartyController::$config['compile_dir'] = S2BASE_PHP5_ROOT . '/var/smarty/template_c';
S2Base_SmartyController::$config['config_dir']  = S2BASE_PHP5_ROOT . '/var/smarty/config';
S2Base_SmartyController::$config['cache_dir']   = S2BASE_PHP5_ROOT . '/var/smarty/cache';
S2Base_SmartyController::$config['caching']     = 0;
?>
