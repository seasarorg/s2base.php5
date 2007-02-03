<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action 
{
    private $service = null;
    private $view = null;

    public function init() {
        if (Zend::isRegistered(S2Base_ZfDispatcherSupportPlugin::VIEW_REGISTRY_KEY)) {
            $this->view = Zend::registry(S2Base_ZfDispatcherSupportPlugin::VIEW_REGISTRY_KEY);
        }
    }

    public function preDispatch() {}

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
