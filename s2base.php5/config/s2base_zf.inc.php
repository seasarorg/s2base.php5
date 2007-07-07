<?php
/** 定数定義 */
define('S2BASE_PHP5_ZF_TPL_SUFFIX', 'phtml');
define('S2BASE_PHP5_ZF_DEFAULT_MODULE', 'default');
define('S2BASE_PHP5_ZF_APP_DICON', S2BASE_PHP5_ROOT . '/app/commons/dicon/zf.dicon');
define('S2BASE_PHP5_PLUGIN_ZF',S2BASE_PHP5_ROOT . '/vendor/plugins/zf');
//define('S2BASE_PHP5_LAYOUT', S2BASE_PHP5_ROOT . '/app/commons/view/layout.tpl');

/** ライブラリ設定 */
require_once('Smarty/libs/Smarty.class.php');
require_once('Zend/Loader.php');
require_once('Zend/Registry.php');
require_once('Zend/Controller/Front.php');
require_once('Zend/Controller/Request/Http.php');
require_once('Zend/Controller/Response/Http.php');
require_once('Zend/Controller/Dispatcher/Standard.php');
require_once('Zend/Session.php');
require_once('Zend/Db.php');
require_once('Zend/Db/Table.php');
require_once('Zend/Config/Ini.php');
require_once('Zend/View.php');
require_once('Zend/Log.php');
require_once('Zend/Log/Writer/Stream.php');
require_once('Zend/Log/Filter/Priority.php');
require_once('Zend/Acl.php');
require_once('Zend/Acl/Resource.php');
require_once('Zend/Acl/Role.php');
require_once('Zend/Auth.php');
require_once('Zend/Auth/Adapter/Interface.php');

require_once(S2BASE_PHP5_PLUGIN_ZF . '/s2base_zf.core.php');
S2ContainerClassLoader::import(S2BASE_PHP5_PLUGIN_ZF . '/classes');

/** Zned_Log 設定 */
define('S2CONTAINER_PHP5_LOG_LEVEL', Zend_Log::INFO);
define('S2BASE_PHP5_ZF_LOG_STREAM', S2BASE_PHP5_VAR_DIR . '/logs/zf.log');
define('S2BASE_PHP5_ZF_LOG_PRIORITY', S2CONTAINER_PHP5_LOG_LEVEL);
//define('S2CONTAINER_PHP5_DEBUG_EVAL', true);

/** session save path 設定 */
session_save_path(S2BASE_PHP5_VAR_DIR . '/session');

/**
 * Smarty 設定
 */
define('S2BASE_PHP5_USE_SMARTY', false);

/** S2Base_Zf 設定 */
class S2Base_ZfInitialize {
    public static function init() {
        /** Zend_Log 設定 */
            self::initLogger();
        /** Zend_Cache 設定 */
            self::initCache();
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

    public static function initTest() {
        self::init();
    }

    public static function initLogger() {
        /** Zend_Log 設定 */
        S2Container_S2Logger::$LOGGER_FACTORY = 'S2Container_ZendLoggerFactory';
        S2Container_S2Logger::setLoggerFactory(null);
        $logger = S2Container_S2Logger::getLogger('s2base_zf');
        $writer = new Zend_Log_Writer_Stream(S2BASE_PHP5_ZF_LOG_STREAM);
        $writer->setFormatter(new S2Base_ZfSimpleLogFormatter());
        $logger->addWriter($writer);
        $logger->addFilter(new Zend_Log_Filter_Priority(S2BASE_PHP5_ZF_LOG_PRIORITY));
        Zend_Registry::set('logger', $logger);
    }

    public static function initCache() {
        define('S2CONTAINER_PHP5_CACHE_SUPPORT_CLASS', 'S2Container_ZendCacheSupport');
        define('S2CONTAINER_PHP5_ZEND_CACHE_INI', S2BASE_PHP5_ROOT . '/config/cache.ini');
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
            Zend_Controller_Action_HelperBroker::addHelper(new S2Base_ZfStandardViewRenderer($view));
        }
    }

    public static function initFrontController(Zend_Controller_Front $fc) {
        $fc->addModuleDirectory(S2BASE_PHP5_ROOT . '/app/modules');
        $fc->setDispatcher(new S2Base_ZfDispatcherImpl());
        $fc->throwExceptions(true);
        $fc->setDefaultModule(S2BASE_PHP5_ZF_DEFAULT_MODULE);
        /** プラグイン設定 */
        $plugin = new S2Base_ZfValidateSupportPlugin();
        $plugin->addValidateFactory(new S2Base_ZfRegexValidateFactory());
        $fc->registerPlugin($plugin);
    }
}
