<?php
require_once('Zend/Controller/Plugin/Abstract.php');
class S2Base_ZfDispatcherSupportPlugin extends Zend_Controller_Plugin_Abstract
{
    private static $exitDispatchLoop = false;

    public static function setExitDispatchLoop($val = true) {
        self::$exitDispatchLoop = $val;
    }

    public function postDispatch($request) {
        if (self::$exitDispatchLoop) {
            $request->setDispatched();
        }
    }
}
?>
