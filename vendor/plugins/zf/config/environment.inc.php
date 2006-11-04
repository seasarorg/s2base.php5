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
 * Library setting
 */
ini_set('include_path',  S2BASE_PHP5_ROOT . '/lib/ZendFramework/library'
        . PATH_SEPARATOR . ini_get('include_path')
);
require_once('Zend.php');

/**
 * definition
 */
define('S2BASE_PHP5_ZF_TPL_SUFFIX','.html'); 
define('S2BASE_PHP5_ZF_ALIAS','/zf'); 

/**
 * Directory setting
 */
define('S2BASE_PHP5_PLUGIN_ZF',S2BASE_PHP5_ROOT . '/vendor/plugins/zf');

/**
 * session path setting
 */
session_save_path(S2BASE_PHP5_VAR_DIR . '/session');

?>
