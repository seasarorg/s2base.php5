<?php
/**
 * Directory setting
 */
define('S2BASE_PHP5_ROOT', '@@S2BASE_PHP5_ROOT@@');
define('S2BASE_PHP5_VAR_DIR',S2BASE_PHP5_ROOT . '/var');

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
 * library setting
 */
require_once('S2Container/S2Container.php');
require_once('S2Dao/S2Dao.php');

/**
 * Log setting
 */
define('S2CONTAINER_PHP5_LOG_LEVEL', S2Container_SimpleLogger::WARN);
//define('S2CONTAINER_PHP5_SIMPLE_LOG_FILE',S2BASE_PHP5_VAR_DIR . '/logs/s2.log');
//define('S2CONTAINER_PHP5_DEBUG_EVAL',false);

//define('LOG4PHP_DIR', S2BASE_PHP5_ROOT . '/lib/log4php-0.9/src/log4php');
//define('LOG4PHP_CONFIGURATION', S2BASE_PHP5_ROOT . '/config/log4php.properties');
//require_once(LOG4PHP_DIR . '/LoggerManager.php');
//S2Container_S2LogFactory::$LOGGER = S2Container_S2LogFactory::LOG4PHP;