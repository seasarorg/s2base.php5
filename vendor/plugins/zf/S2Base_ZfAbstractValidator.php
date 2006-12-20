<?php
abstract class S2Base_ZfAbstractValidator {
    protected $request = null;
    protected $response = null;
    protected $controllerName = null;
    protected $moduleName = null;
    protected $actionName = null;
    protected $rule = null;
    protected $view = null;
    protected $iniFile = null;

    abstract public function validate(Zend_Controller_Request_Abstract $request,
                                      Zend_View_Interface $view);

    public function __construct(){}

    public function setIniFile($file) {
        $this->iniFile = $file;
    }

    public function setRule(array $rule){
        $this->rule = $rule;
    }

    protected function initialize(Zend_Controller_Request_Abstract $request,
                                  Zend_View_Interface $view){
        $this->request = $request;
        $this->view = $view;
        $this->response = $this->view->getResponse();
        $this->moduleName = S2Base_ZfDispatcherSupportPlugin::getModuleName($request);
        $this->controllerName = $this->request->getControllerName();
        $this->actionName = $this->request->getActionName();
    }
}
?>
