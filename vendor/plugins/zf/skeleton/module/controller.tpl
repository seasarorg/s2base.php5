<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action {
    private $service = null;

    public function indexAction() {}
    /** S2BASE_PHP5 ACTION METHOD **/

    public function __call($methodName, $args) {
        return parent::__call($methodName, $args);
    }

    public function setService(@@SERVICE_CLASS_NAME@@ $service) {
        $this->service = $service;
    }
}
?>
