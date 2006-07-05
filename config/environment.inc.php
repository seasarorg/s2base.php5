<?php
/**
 * Directory setting
 */
define('S2BASE_PHP5_ROOT',dirname(dirname(__FILE__)));
define('S2BASE_PHP5_VAR_DIR',S2BASE_PHP5_ROOT . '/var');

/**
 * Log level setting
 */
//define('S2CONTAINER_PHP5_LOG_LEVEL', S2Container_SimpleLogger::WARN);

/**
 * dicon file suffix setting
 */
define('S2BASE_PHP5_DICON_SUFFIX','.dicon');

/**
 * S2Dao pdo.dicon setting
 */
define('PDO_DICON',S2BASE_PHP5_ROOT . '/app/commons/dicon/pdo.dicon');

/**
 * DTD validation setting
 */
define('S2CONTAINER_PHP5_DOM_VALIDATE',false);

/**
 * include path setting
 */
$packages = array(
    S2BASE_PHP5_ROOT . '/app/commons/dao',
    S2BASE_PHP5_ROOT . '/app/commons/interceptor',
    S2BASE_PHP5_ROOT . '/lib'
);
ini_set('include_path', 
        implode(PATH_SEPARATOR, $packages) . PATH_SEPARATOR . 
        ini_get('include_path')
);

/**
 * library setting
 */
require_once('S2Container/S2Container.php');
require_once('S2Dao/S2Dao.php');

/**
 * autoload setting
 */
function __autoload($class = null){
    if($class != null){@include_once("$class.class.php");}
}

?>
