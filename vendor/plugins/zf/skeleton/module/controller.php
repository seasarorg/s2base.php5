<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action 
{
    private $service;
    private $view;

    public function init() {
        if ($this->getRequest()->getControllerName() == null) {
            $this->getRequest()->setControllerName('@@CONTROLLER_NAME@@');
        }
        $this->view = new S2Base_ZfSmartyView();
        $this->view->setRequest($this->getRequest());
        $this->view->setResponse($this->getResponse());
    }

    public function __call($methodName, $args) {
        $this->view->render($this->getRequest()->getActionName());
    }

    public function indexAction() {
        $this->view->render('@@TEMPLATE_NAME@@');
    }
    /** S2BASE_PHP5 ACTION METHOD **/

    public function preDispatch() {
        S2Base_ZfRequestValidator::execute($this->getRequest(), $this->view);
    }

    public function postDispatch() {
        $this->view->prepareResponse();
    }

    public function setService(@@SERVICE_CLASS_NAME@@ $service) {
        $this->service = $service;
    }

    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
        if ($this->view instanceof S2Base_ZfSmartyView) {
            $this->view->setRequest($this->getRequest());
            $this->view->setResponse($this->getResponse());
        }
    }
}
?>
