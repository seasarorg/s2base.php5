<?php
/**
 * definition
 */
define('S2BASE_PHP5_PRADO_PAGE_SUFFIX','.page');
define('S2BASE_PHP5_PRADO_ASSETS_DIR',S2BASE_PHP5_DS .'assets' .S2BASE_PHP5_DS); 
define('S2BASE_PHP5_PRADO_PROTECTED_DIR',S2BASE_PHP5_DS .'protected'.S2BASE_PHP5_DS); 
define('S2BASE_PHP5_PRADO_PAGES_DIR',S2BASE_PHP5_PRADO_PROTECTED_DIR .'pages'.S2BASE_PHP5_DS); 
define('S2BASE_PHP5_PRADO_DICON_DIR',S2BASE_PHP5_PRADO_PROTECTED_DIR .'dicon'.S2BASE_PHP5_DS); 
define('S2BASE_PHP5_PRADO_RUNTIME_DIR',S2BASE_PHP5_PRADO_PROTECTED_DIR .'runtime'.S2BASE_PHP5_DS); 
define('S2BASE_PHP5_PLUGIN_PRADO',S2BASE_PHP5_ROOT.'/vendor/plugins/prado');

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
 * session path setting
 */
session_save_path(S2BASE_PHP5_VAR_DIR . '/session');

?>
