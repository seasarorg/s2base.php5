<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action 
{
    private $service = null;
    private $view = null;

    public function init() {
        if (Zend_Registry::isRegistered(S2Base_ZfDispatcherSupportPlugin::VIEW_REGISTRY_KEY)) {
            $this->view = Zend_Registry::set(S2Base_ZfDispatcherSupportPlugin::VIEW_REGISTRY_KEY);
        }
    }

    public function preDispatch() {}

    public function __call($methodName, $args) {}

    public function indexAction() {
        $ctls = array(@@CONTROLLERS@@);
        $this->view->assign('ctls', $ctls);
    }
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
