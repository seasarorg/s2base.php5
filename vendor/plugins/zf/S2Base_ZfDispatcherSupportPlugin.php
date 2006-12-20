<?php
require_once('Zend/Controller/Plugin/Abstract.php');
class S2Base_ZfDispatcherSupportPlugin extends Zend_Controller_Plugin_Abstract
{
    const VIEW_REGISTRY_KEY = 's2base_view';
    public static $VIEW_CLASS = 'S2Base_ZfSmartyView';
    private static $exitDispatchLoop = false;

    public static function setExitDispatchLoop($val = true) {
        self::$exitDispatchLoop = $val;
    }

    public static function getModuleName(Zend_Controller_Request_Abstract $request) {
        if (S2BASE_PHP5_ZF_USE_MODULE) {
            return $request->getParam('module',false);
        }
        return S2BASE_PHP5_ZF_DEFAULT_MODULE;
    }

    public function dispatchLoopStartup($request) {
        if (self::$VIEW_CLASS != null) {
            Zend::register(self::VIEW_REGISTRY_KEY, new self::$VIEW_CLASS);
        }
    }

    public function dispatchLoopShutdown() {
        if (Zend::isRegistered(self::VIEW_REGISTRY_KEY)) {
            $view = Zend::registry(self::VIEW_REGISTRY_KEY);
            if ($view instanceof S2Base_ZfView) {
                $view->renderWithTpl();
            }
        }
    }

    public function preDispatch($request) {
        $moduleName = self::getModuleName($request);
        Zend_Controller_Front::getInstance()->getDispatcher()->
            addControllerDirectory(S2BASE_PHP5_ROOT . '/app/modules/' . $moduleName,
                                   $moduleName);
    }

    public function postDispatch($request) {
        if (self::$exitDispatchLoop) {
            $request->setDispatched();
        }
    }
}
?>
