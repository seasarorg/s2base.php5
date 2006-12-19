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

    public function dispatchLoopStartup($request) {
        Zend::register(self::VIEW_REGISTRY_KEY, new self::$VIEW_CLASS);
    }

    public function dispatchLoopShutdown() {
        $view = Zend::registry(self::VIEW_REGISTRY_KEY);
        if ($view instanceof S2Base_ZfView) {
            $view->renderWithTpl();
        }
    }

    public function preDispatch($request) {}

    public function postDispatch($request) {
        if (self::$exitDispatchLoop) {
            $request->setDispatched();
        }
    }
}
?>
