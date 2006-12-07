<?php
/**
 * Directory setting
 */
define('S2BASE_PHP5_ROOT',dirname(dirname(__FILE__)));
if (!defined('S2BASE_PHP5_VAR_DIR')) {
    define('S2BASE_PHP5_VAR_DIR',S2BASE_PHP5_ROOT . '/var');
}

/**
 * suffix setting
 */
if (!defined('S2BASE_PHP5_DICON_SUFFIX')) {
    define('S2BASE_PHP5_DICON_SUFFIX','.dicon');
}
if (!defined('S2BASE_PHP5_CLASS_SUFFIX')){
    define('S2BASE_PHP5_CLASS_SUFFIX','.class.php');
}

/**
 * S2Dao pdo.dicon setting
 */
if (!defined('PDO_DICON')) {
    define('PDO_DICON',S2BASE_PHP5_ROOT . '/app/commons/dicon/pdo.dicon');
}

/**
 * DTD validation setting
 */
define('S2CONTAINER_PHP5_DOM_VALIDATE',false);

/**
 * include path setting
 */
$packages = array(S2BASE_PHP5_ROOT . '/lib');
ini_set('include_path', 
        implode(PATH_SEPARATOR, $packages) . PATH_SEPARATOR . 
        ini_get('include_path')
);

/**
 * library setting
 */
require_once('S2Container/S2Container.php');
S2ContainerClassLoader::import(S2CONTAINER_PHP5);
require_once('S2Dao/S2Dao.php');
S2ContainerClassLoader::import(S2DAO_PHP5);
//require_once('S2Javelin/S2Javelin.php');
//S2ContainerClassLoader::import(S2JAVELIN_PHP5);
S2ContainerClassLoader::import(S2BASE_PHP5_ROOT . '/app/commons/dao');

/**
 * autoload setting
 */
require_once('S2ContainerAutoLoad.php');

/**
 * Log level setting
 */
//define('S2CONTAINER_PHP5_LOG_LEVEL', S2Container_SimpleLogger::DEBUG);
//define('S2CONTAINER_PHP5_DEBUG_EVAL',false);

/**
 * S2Container_SimpleLogger log file setting
 */
//define('S2CONTAINER_PHP5_SIMPLE_LOG_FILE',S2BASE_PHP5_VAR_DIR . '/logs/s2.log');

/**
 * log4php setting
 */
//S2Container_S2LogFactory::$LOGGER = S2Container_S2LogFactory::LOG4PHP;
//define('LOG4PHP_DIR', S2BASE_PHP5_ROOT . '/lib/log4php-version/src/log4php');
//define('LOG4PHP_CONFIGURATION', S2BASE_PHP5_ROOT . '/config/log4php.properties');
//require_once(LOG4PHP_DIR . '/LoggerManager.php');
?>
