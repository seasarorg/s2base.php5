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
 * include path setting for PRADO Plugin
 */
require_once('S2ContainerSplAutoLoad.php');
$packages = array(S2BASE_PHP5_ROOT . '/vendor/plugins/prado');
ini_set('include_path', 
        implode(PATH_SEPARATOR, $packages) . PATH_SEPARATOR . 
        ini_get('include_path')
);

/**
 * Library setting
 */
require_once('prado/framework/prado.php');
require_once('S2Base_Prado.class.php');

/**
 * definition
 */
define('S2BASE_PHP5_PRADO_PAGE_SUFFIX','.page'); 

/**
 * session path setting
 */
session_save_path(S2BASE_PHP5_VAR_DIR . '/session');

?>