<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action 
{
    private $service;
    private $view;

    public function init() {
        $this->view = Zend::registry(S2Base_ZfDispatcherSupportPlugin::VIEW_REGISTRY_KEY);
    }

    public function preDispatch() {
        S2Base_ZfRequestValidator::execute($this->getRequest(), $this->view);
    }

    public function __call($methodName, $args) {}

    public function indexAction() {}
    /** S2BASE_PHP5 ACTION METHOD **/

    public function postDispatch() {}

    public function setService(@@SERVICE_CLASS_NAME@@ $service) {
        $this->service = $service;
    }

    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
}
?>
