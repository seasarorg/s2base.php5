<?php
require_once('Zend/View/Interface.php');
class S2Base_ZfSmartyView
    extends Smarty
    implements Zend_View_Interface {

    public static $config = array();
    private static $rendered = false;
    private static $errors = array();
    private $layout = '';
    private $scriptPath = '';
    private $request = null;
    private $response = null;
    private $template = null;

    public function __construct(){
        parent::__construct();
        foreach(self::$config as $key=>$val){
            $this->$key = $val;
        }
        $this->setScriptPath(S2BASE_PHP5_ROOT . '/app/modules');
        if(defined('S2BASE_PHP5_LAYOUT')){
            $this->layout = S2BASE_PHP5_LAYOUT;
        }

        $this->request = Zend_Controller_Front::getInstance()->getRequest();
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
    }

    public static function setRendered($value = true){
        self::$rendered = $value;
    }

    public static function isRendered(){
        return self::$rendered;
    }

    public function setLayout($layout){
        $this->layout = $layout;
    }

    public function putError($key,$val){
        self::$errors[$key] = $val;
    }

    public function getError($key){
        if(isset(self::$errors[$key])){
            return self::$errors[$key];
        }
        return null;
    }

    public function getErrors(){
        return self::$errors;
    }

    public function setRequest(Zend_Controller_Request_Abstract $request){
        $this->request = $request;
    }

    public function setResponse(Zend_Controller_Response_Abstract $response){
        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getEngine() {
        return $this;
    }

    public function setScriptPath($path) {
        $this->scriptPath = $path;
    }

    public function setTpl($tpl) {
        $this->template = $tpl;
    }

    public function getTpl() {
        return $this->template;
    }

    public function prepareResponse() {
        if ($this->template == null) {
            $this->render($this->request->getActionName());
        } else {
            $this->render($this->template);
        }
    }

    /**
     * @see Zend_View_Interface::__set()
     */
    public function __set($key, $val){}

    /**
     * @see Zend_View_Interface::__get()
     */
    public function __get($key){}

    /**
     * @see Zend_View_Interface::__isset()
     */
    public function __isset($key){}

    /**
     * @see Zend_View_Interface::__unset()
     */
    public function __unset($key){}

    /**
     * @see Zend_View_Interface::__clearVars()
     */
    public function clearVars(){}

    /**
     * @see Zend_View_Interface::render()
     */
    public function render($name) {
        if (!$this->response instanceof Zend_Controller_Response_Abstract) {
            throw new Exception('response not set.');
        }

        if (self::isRendered()) {
            return;
        }
        self::setRendered();
        
        $controllerName = Zend_Controller_Front::getInstance()->
                          getDispatcher()->getControllerName($this->request);
        $this->template_dir = $this->scriptPath;
        $this->assign('request',$this->request);
        $this->assign('errors',self::$errors);
        $this->assign('module', $controllerName);
        $this->assign('controller', $controllerName);
        $this->assign('action', $this->request->getActionName());
        $this->assign('base_url', $this->request->getBaseUrl());
        $ctl_url = $this->request->getBaseUrl() . '/' . $controllerName;
        $act_url = $ctl_url . '/' . $this->request->getActionName();
        $this->assign('ctl_url', $ctl_url);
        $this->assign('act_url', $act_url);

        if (preg_match('/^file:/',$name)){
            $viewFile = $name;
        } else {
            if (!preg_match('/' . S2BASE_PHP5_ZF_TPL_SUFFIX . '$/', $name)) {
                $name .= S2BASE_PHP5_ZF_TPL_SUFFIX;
            }
            $viewFile = $controllerName
                      . DIRECTORY_SEPARATOR
                      . 'view'
                      . DIRECTORY_SEPARATOR
                      . $name;
            if (!file_exists($this->template_dir . DIRECTORY_SEPARATOR . $viewFile)) {
                throw new Exception('template file not found. [' . 
                     $this->template_dir . DIRECTORY_SEPARATOR . $viewFile . ']');
            }
        }

        if($this->layout == null){
            $this->response->setBody($this->fetch($viewFile));
        }else{
            $this->assign('content_for_layout',$viewFile);
            $this->response->setBody($this->fetch($this->layout));
        }
    }
}
?>
