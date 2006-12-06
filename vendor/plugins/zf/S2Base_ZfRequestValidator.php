<?php
class S2Base_ZfRequestValidator {

    private function __construct(){}

    public static function execute(Zend_Controller_Request_Abstract $request,
                                   Zend_View_Interface $view) {
        $controllerName = Zend_Controller_Front::getInstance()->getDispatcher()->getControllerName($request);
        require_once(S2BASE_PHP5_ROOT . "/app/modules/$controllerName/$controllerName.inc.php");
        $validators = S2Base_ZfValidatorFactory::create($request);
        foreach ($validators as $validator) {
            if(! $validator->validate($request, $view)) {
                break;
            }
        }
    }
}
?>
