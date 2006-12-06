<?php
require_once('Zend/Controller/Plugin/Abstract.php');
class S2Base_ZfDispatcherSupportPlugin extends Zend_Controller_Plugin_Abstract
{
    const VIEW_REGISTRY_KEY = 's2base_view';
    private static $exitDispatchLoop = false;

    public static function setExitDispatchLoop($val = true) {
        self::$exitDispatchLoop = $val;
    }

    public function dispatchLoopStartup($request) {
        Zend::register(self::VIEW_REGISTRY_KEY, new S2Base_ZfSmartyView());
    }

    public function dispatchLoopShutdown() {
        Zend::registry(self::VIEW_REGISTRY_KEY)->prepareResponse();
    }

    public function preDispatch($request) {
        //S2Base_ZfRequestValidator::execute(Zend_Controller_Front::getInstance()->getRequest(),
        //                                   Zend::registry(self::VIEW_REGISTRY_KEY));
    }

    public function postDispatch($request) {
        if (self::$exitDispatchLoop) {
            $request->setDispatched();
        }
    }
}
?>
