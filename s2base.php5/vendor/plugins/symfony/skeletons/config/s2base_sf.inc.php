<?php
//----- S2Base 設定 -----------------------------------------------------------
/**
 * S2Base Root ディレクトリ設定
 */
define('S2BASE_PHP5_ROOT',dirname(dirname(__FILE__)));

/**
 * ダイコンファイル拡張子設定
 */
if (!defined('S2BASE_PHP5_DICON_SUFFIX')) {
    define('S2BASE_PHP5_DICON_SUFFIX','.dicon');
}

/**
 * クラスファイル拡張子設定
 */
if (!defined('S2BASE_PHP5_CLASS_SUFFIX')){
    define('S2BASE_PHP5_CLASS_SUFFIX','.class.php');
}


//----- PHP環境設定 -----------------------------------------------------------
/**
 * include_path 設定
 */
$packages = array(S2BASE_PHP5_ROOT . '/lib');
ini_set('include_path', 
        implode(PATH_SEPARATOR, $packages) . PATH_SEPARATOR . 
        ini_get('include_path')
);

/**
 * PHP ロギング設定
 */
error_reporting(E_ERROR);
error_reporting(E_WARNING);
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');


//----- ライブラリ設定 -------------------------------------------------------
/**
 * ライブラリのロード
 */
require_once('S2Container/S2Container.php');
require_once('S2Dao/S2Dao.php');

/**
 * autoload 設定
 */
require_once('S2ContainerSplAutoLoad.php');
S2ContainerClassLoader::import(S2DAO_PHP5);

//----- S2Container 設定 -------------------------------------------------------
/**
 * S2Container ロギング設定
 */
//define('S2CONTAINER_PHP5_LOG_LEVEL', S2Container_SimpleLogger::DEBUG);
//define('S2CONTAINER_PHP5_DEBUG_EVAL', true);
//define('S2CONTAINER_PHP5_SIMPLE_LOG_FILE',S2BASE_PHP5_VAR_DIR . '/logs/s2.log');

/**
 * S2Container DI設定
 */
define('S2CONTAINER_PHP5_PERMIT_CLASS_INJECTION', true);

/**
 * S2Container DTD 検証設定
 */
define('S2CONTAINER_PHP5_DOM_VALIDATE',false);

/**
 * S2Container 環境設定
 */
//define('S2CONTAINER_PHP5_ENV', 'test');


//----- S2Dao 設定 ------------------------------------------------------------
/**
 * S2Dao pdo.dicon 設定
 */
if (!defined('PDO_DICON')) {
    define('PDO_DICON',S2BASE_PHP5_ROOT . '/config/pdo.dicon');
}

//----- with Symfony 設定 ------------------------------------------------------
define('S2BASE_PHP5_PLUGIN_SF',S2BASE_PHP5_ROOT . '/vendor/plugins/symfony');