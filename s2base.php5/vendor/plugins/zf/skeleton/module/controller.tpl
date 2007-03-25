<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action {
    private $service = null;

    public function indexAction() {}
    /** S2BASE_PHP5 ACTION METHOD **/

    public function __call($methodName, $args) {}

    public function preDispatch() {}

    public function postDispatch() {
        $this->render($this->getRequest()->getActionName());
    }

    public function setService(@@SERVICE_CLASS_NAME@@ $service) {
        $this->service = $service;
    }

    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
        if ($this->view instanceof S2Base_ZfView) {
            $this->viewSuffix = S2BASE_PHP5_ZF_TPL_SUFFIX;
        }
    }

    public function render($script, $name = null) {
        parent::render($script, $name, true);
    }
}
?>
