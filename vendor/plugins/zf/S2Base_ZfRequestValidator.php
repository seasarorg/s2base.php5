<?php
class S2Base_ZfRequestValidator {

    private function __construct(){}

    public static function execute(Zend_Controller_Request_Abstract $request,
                                   Zend_View_Interface $view) {
        $validators = S2Base_ZfValidatorFactory::create($request);
        foreach ($validators as $validator) {
            if(! $validator->validate($request, $view)) {
                break;
            }
        }
    }
}
?>
