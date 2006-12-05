<?php
abstract class S2Base_ZfValidateFilter {
    const VALIDATE_DIR = '/validate/';
    protected $request = null;
    protected $response = null;
    protected $controllerName = null;
    protected $moduleName = null;
    protected $actionName = null;
    protected $rule = null;
    protected $view = null;

    abstract public function validate();

    abstract public function getSuffix();

    public function __construct(Zend_Controller_Request_Abstract $request,
                                Zend_Controller_Response_Abstract $response,
                                Zend_View_Interface $view){
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->controllerName = $this->request->getControllerName();
        $this->moduleName = $this->controllerName;
        $this->actionName = $this->request->getActionName();
    }

    public function includeRule(){
        $ruleFile = S2BASE_PHP5_ROOT
                  . '/app/modules/' 
                  . $this->moduleName
                  . self::VALIDATE_DIR
                  . $this->request->getParam(S2Base_ZfDispatcher::ACTION_METHOD)
                  . '.'
                  . $this->getSuffix()
                  . '.ini';
        if(is_readable($ruleFile)){
            $this->rule = parse_ini_file($ruleFile,true);
        }
    }
}
?>
