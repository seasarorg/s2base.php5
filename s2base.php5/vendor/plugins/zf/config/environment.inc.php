<?php
/**
 * php log setting
 */
error_reporting(E_ERROR);
error_reporting(E_WARNING);
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
ini_set('error_log', S2BASE_PHP5_VAR_DIR . '/logs/php.log');

/**
 * definition
 */
define('S2BASE_PHP5_ZF_TPL_SUFFIX', 'html'); 
define('S2BASE_PHP5_ZF_DEFAULT_MODULE', 'default'); 
define('S2BASE_PHP5_PLUGIN_ZF',S2BASE_PHP5_ROOT . '/vendor/plugins/zf');
//define('S2BASE_PHP5_LAYOUT', S2BASE_PHP5_ROOT . '/app/commons/view/layout.tpl'); 

/**
 * session path setting
 */
session_save_path(S2BASE_PHP5_VAR_DIR . '/session');

/**
 * Library setting
 */
//require_once('PHPUnit2/Framework/TestCase.php');
require_once('Smarty/libs/Smarty.class.php');

require_once('Zend/Loader.php');
require_once('Zend/Registry.php');
require_once('Zend/Controller/Front.php');
require_once('Zend/Controller/Request/Http.php');
require_once('Zend/Controller/Dispatcher/Standard.php');
require_once('Zend/Session.php');
require_once('Zend/Db.php');
require_once('Zend/Db/Table.php');
require_once('Zend/Config/Ini.php');
require_once('Zend/View.php');
require_once(S2BASE_PHP5_PLUGIN_ZF . '/s2base_zf.core.php');
S2ContainerClassLoader::import(S2BASE_PHP5_PLUGIN_ZF . '/classes');

/**
 * Smarty 設定
 */
define('S2BASE_PHP5_USE_SMARTY', false);


/** S2Base_Zf 設定 */
class S2Base_ZfInitialize {
    public static function init() {
        /** Zend_DB DefaultAdaptor 設定 */
            S2Base_ZfDb::setDefaultPdoAdapter();
        /** ViewRenderer 設定 */
            self::initViewRenderer();
        /** リクエスト設定 */
            $request = new Zend_Controller_Request_Http();
            $request->setBaseUrl();
        /** フロントコントローラ設定 */
            $fc = Zend_Controller_Front::getInstance();
            $fc->setRequest($request);
            self::initFrontController($fc);
    }

    public static function initViewRenderer() {
        /** ViewRenderer 設定 */
        Zend_Controller_Action_HelperBroker::resetHelpers();
        if (defined('S2BASE_PHP5_USE_SMARTY') and S2BASE_PHP5_USE_SMARTY) {
            /* S2Base_ZfSmartyViewRenderer::$config['property name'] = property value */
            S2Base_ZfSmartyViewRenderer::$config['compile_dir'] = S2BASE_PHP5_ROOT . '/var/smarty/template_c';
            S2Base_ZfSmartyViewRenderer::$config['config_dir']  = S2BASE_PHP5_ROOT . '/var/smarty/config';
            S2Base_ZfSmartyViewRenderer::$config['cache_dir']   = S2BASE_PHP5_ROOT . '/var/smarty/cache';
            S2Base_ZfSmartyViewRenderer::$config['caching']     = 0;
            Zend_Controller_Action_HelperBroker::addHelper(
                new S2Base_ZfSmartyViewRenderer());
        } else {
            $view = new Zend_View();
            $view->addBasePath(S2BASE_PHP5_ROOT . '/app/commons/view');
            Zend_Controller_Action_HelperBroker::addHelper(
                new S2Base_ZfStandardViewRenderer($view,
                    array('viewBasePathSpec'   => ':moduleDir/:module/:controller/view',
                          'viewScriptPathSpec' => ':action.' . S2BASE_PHP5_ZF_TPL_SUFFIX)));
        }
    }

    public static function initFrontController(Zend_Controller_Front $fc) {
        $fc->setModuleControllerDirectoryName('');
        $fc->addModuleDirectory(S2BASE_PHP5_ROOT . '/app/modules');
        $fc->setDispatcher(new S2Base_ZfDispatcher());
        $fc->throwExceptions(true);
        $fc->setDefaultModule(S2BASE_PHP5_ZF_DEFAULT_MODULE);
        /** プラグイン設定 */
        $plugin = new S2Base_ZfValidateSupportPlugin();
        $plugin->addValidateFactory(new S2Base_ZfRegexValidateFactory());
        $fc->registerPlugin($plugin);
   }
}
?>
