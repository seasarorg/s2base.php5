<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action 
{
    private $service;
    private $view;

    public function init()
    {
        if ($this->getRequest()->getControllerName() == null) {
            $this->getRequest()->setControllerName('@@CONTROLLER_NAME@@');
        }
        $this->view = new S2Base_ZfSmartyView();
        $this->view->setRequest($this->getRequest());
    }

    public function __call($methodName, $args)
    {
        $this->getResponse()->setBody($this->view->render(
            $this->getRequest()->getActionName()));
    }

    public function indexAction()
    {
        $this->getResponse()->setBody($this->view->render('@@TEMPLATE_NAME@@'));
    }
    /** S2BASE_PHP5 ACTION METHOD **/

    public function preDispatch() {}

    public function postDispatch() {}

    public function setService(@@SERVICE_CLASS_NAME@@ $service)
    {
        $this->service = $service;
    }

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
?>
