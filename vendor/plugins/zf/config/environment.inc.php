<?php
/**
 * php log setting
 */
error_reporting(E_ERROR);
error_reporting(E_WARNING);
error_reporting(E_ALL);
ini_set('display_errors','On');
ini_set('log_errors','On');
ini_set('error_log',S2BASE_PHP5_VAR_DIR . '/logs/php.log');

/**
 * definition
 */
define('S2BASE_PHP5_ZF_TPL_SUFFIX','.html'); 
define('S2BASE_PHP5_ZF_USE_MODULE', false); 
define('S2BASE_PHP5_ZF_DEFAULT_MODULE','Default'); 

/**
 * Directory setting
 */
define('S2BASE_PHP5_PLUGIN_ZF',S2BASE_PHP5_ROOT . '/vendor/plugins/zf');

/**
 * session path setting
 */
session_save_path(S2BASE_PHP5_VAR_DIR . '/session');

/**
 * Library setting
 */
require_once('Smarty/libs/Smarty.class.php');
require_once('Zend.php');
require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfDispatcher.php';
require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfDispatcherSupportPlugin.php';
require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfView.php';
require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfSmartyView.php';

/**
 * Smarty config
 *     S2Base_ZfSmartyView::$config['property name'] = property value
 */
S2Base_ZfSmartyView::$config['compile_dir'] = S2BASE_PHP5_ROOT . '/var/smarty/template_c';
S2Base_ZfSmartyView::$config['config_dir']  = S2BASE_PHP5_ROOT . '/var/smarty/config';
S2Base_ZfSmartyView::$config['cache_dir']   = S2BASE_PHP5_ROOT . '/var/smarty/cache';
S2Base_ZfSmartyView::$config['caching']     = 0;

/**
 * for S2Base_ZfDefaultView
 */
//require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfDefaultView.php';
//S2Base_ZfDispatcherSupportPlugin::$VIEW_CLASS = 'S2Base_ZfDefaultView';

/**
 * for Zend_View
 */
//S2Base_ZfDispatcherSupportPlugin::$VIEW_CLASS = null;
?>
