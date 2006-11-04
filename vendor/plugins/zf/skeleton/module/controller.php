<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action 
{
    private $service;
    private $view;

    public function __construct(){
        $this->view = new Zend_View();
        $this->view->setScriptPAth(S2BASE_PHP5_ROOT . '/app/modules/@@CONTROLLER_NAME@@/view');
    }

    public function setService(@@SERVICE_CLASS_NAME@@ $service)
    {
        $this->service = $service;
    }

    public function setView($view)
    {
        $this->view = $view;
    }

    public function indexAction()
    {
        echo $this->view->render('@@TEMPLATE_NAME@@');
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
    /** S2BASE_PHP5 ACTION METHOD **/
}
?>
