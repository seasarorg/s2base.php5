<?php
class S2Base_ZfSmartyView
    extends Smarty
    implements Zend_View_Interface, S2Base_ZfView {

    public static $config = array();
    private static $rendered = false;
    private static $errors = array();
    private $layout = '';
    private $scriptPath = '';
    private $request = null;
    private $response = null;
    private $template = null;
    private $controllerName = null;
    private $moduleName = null;
    
    public function __construct(){
        parent::__construct();
        foreach(self::$config as $key=>$val){
            $this->$key = $val;
        }

        if(defined('S2BASE_PHP5_LAYOUT')){
            $this->layout = S2BASE_PHP5_LAYOUT;
        }

        $this->request = Zend_Controller_Front::getInstance()->getRequest();
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
        $this->moduleName = S2Base_ZfDispatcherSupportPlugin::getModuleName($this->request);
        $this->setScriptPath(S2BASE_PHP5_ROOT . '/app/modules/');
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

    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    public function renderWithTpl() {
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
        
        if ($this->request->has(S2Base_ZfValidatorSupportPlugin::ERRORS_KEY)) {
            $this->putError('validate',$this->request->getParam(S2Base_ZfValidatorSupportPlugin::ERRORS_KEY));
        }

        $controllerName = $this->controllerName != null ?
                          $this->controllerName :
                          $this->request->getControllerName();
        $this->template_dir = $this->scriptPath;
        $this->assign('request',$this->request);
        $this->assign('errors',self::$errors);
        $this->assign('module', $this->moduleName);
        $this->assign('controller', $controllerName);
        $this->assign('action', $this->request->getActionName());
        $this->assign('base_url', $this->request->getBaseUrl());
        $mod_url = $this->moduleName === S2BASE_PHP5_ZF_DEFAULT_MODULE ?
                   $this->request->getBaseUrl() :
                   $this->request->getBaseUrl() . '/' . $this->moduleName;
        $ctl_url = $mod_url . '/' . $controllerName;
        $act_url = $ctl_url . '/' . $this->request->getActionName();
        $this->assign('mod_url', $mod_url);
        $this->assign('ctl_url', $ctl_url);
        $this->assign('act_url', $act_url);
        $ctlViewDir = $this->moduleName
                    . DIRECTORY_SEPARATOR
                    . $controllerName
                    . DIRECTORY_SEPARATOR
                    . 'view';
        $this->assign('ctl_view_dir', $this->scriptPath . DIRECTORY_SEPARATOR . $ctlViewDir);
        $this->assign('commons_view_dir', S2BASE_PHP5_ROOT . '/app/commons/view');

        if (preg_match('/^file:/',$name)){
            $viewFile = $name;
        } else {
            if (!preg_match('/' . S2BASE_PHP5_ZF_TPL_SUFFIX . '$/', $name)) {
                $name .= S2BASE_PHP5_ZF_TPL_SUFFIX;
            }
            $viewFile = $ctlViewDir . DIRECTORY_SEPARATOR . $name;
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
