<?php
class S2Base_RuntimeException extends Exception {
    public function __construct($id,$args = array()){
        switch($id){
            case 'ERR102':
                $msg = "invalid name [{$args[0]} : {$args[1]}]";
                break;
            case 'ERR103':
                $msg = "module not found [{$args[0]}]";
                break;
            case 'ERR105':
                $msg = "cache dir create fail [{$args[0]}]";
                break;
            case 'ERR106':
                $msg = "invalid redirect target [{$args[0]}]";
                break;
            case 'ERR107':
                $msg = "cycle redirect occured [{$args[0]}] [ " . 
                       implode(' -> ',$args[1]) . 
                       " ] ";
                break;
            case 'ERR108':
                $msg = "invalid action result [{$args[0]}]";
                break;
            case 'ERR109':
                $msg = "template file[{$args[2]}] not found. [ module : {$args[0]}, action : {$args[1]} ]";
                break;
            default:
                $msg = implode($args);
        }
        parent::__construct($msg);
    }   
}

interface S2Base_View {
    public function view();
    public function setLayout($layout);
}

interface S2Base_Request {
    const MAX_LEN = 50;
    public function getModule();
    public function getAction();
    public function getParam($key);
    public function setParam($key,$val);
    public function hasParam($key);
}

interface S2Base_Controller {
    public function process();
    public function setRequest(S2Base_Request $request);
    public function setAction(S2Base_Action $action);
}

interface S2Base_Action {
    public function execute(S2Base_Request $request, S2Base_View $view);
}

interface S2Base_FilterInterceptor {
    public function before();
    public function after($viewName);
}

class S2Base_RequestImpl implements S2Base_Request {
    protected $request = array();
    protected $module;
    protected $action;
    public function __construct() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->request = $_POST;
        } else {
            $this->request = $_GET;
        }
        $this->setModule();
        $this->setAction();
    }
    public function getModule(){
        return $this->module;
    }
    public function getAction(){
        return $this->action;
    }
    public function getParam($key){
        if(isset($this->request[$key])){
            return $this->request[$key];
        }
        return null;
    }
    public function setParam($key,$val){
        $this->request[$key] = $val;
    }
    public function getParams(){
        return $this->request;
    }
    public function hasParam($key){
        return isset($this->request[$key]);
    }
    public function setModule($module = null){
        if ($module == null){
            $this->module = $this->getParam(S2BASE_PHP5_REQUEST_MODULE_KEY);
            if($this->module == null){
                $this->module = S2BASE_PHP5_DEFAULT_MODULE_NAME;
            }
        }else{
            $this->module = $module; 
            $this->setParam(S2BASE_PHP5_REQUEST_MODULE_KEY,$module);
        }
        if(!$this->isValidName($this->module)){
            throw new S2Base_RuntimeException('ERR102',
                                           array(S2BASE_PHP5_REQUEST_MODULE_KEY,
                                                 $this->module));
        }
    }
    public function setAction($action = null){
        if ($action == null){
            $this->action = $this->getParam(S2BASE_PHP5_REQUEST_ACTION_KEY);
            if($this->action == null){
                $this->action = S2BASE_PHP5_DEFAULT_ACTION_NAME;
            }
        }else{
            $this->action = $action;
            $this->setParam(S2BASE_PHP5_REQUEST_ACTION_KEY,$action);
        }
        if(!$this->isValidName($this->action)){
            throw new S2Base_RuntimeException('ERR102',
                                           array(S2BASE_PHP5_REQUEST_ACTION_KEY,
                                                 $this->action));
        }
    }
    protected function isValidName($name){
        if(!preg_match("/^\w+$/",$name)){
            return false;
        }
        if(strlen($name) > S2Base_Request::MAX_LEN){
            return false;
        }
        return true;
    }
}

class S2Base_SmartyController extends Smarty
    implements S2Base_Controller,S2Base_View {
    const TPL_SUFFIX = S2BASE_PHP5_SMARTY_TPL_SUFFIX;
    public static $config = array();
    protected $request = null;
    protected static $errors = array();
    protected static $rendered = false;
    protected $layout = null;
    protected $actionTpl = null;
    protected $action = null;
    public function __construct(){
        parent::__construct();
        foreach(self::$config as $key=>$val){
            $this->$key = $val;
        }
        if(defined('S2BASE_PHP5_LAYOUT')){
            $this->layout = S2BASE_PHP5_LAYOUT;
        }
    }
    public function setAction(S2Base_Action $action){
        $this->action = $action;
    }
    public function setLayout($layout){
        $this->layout = $layout;
    }
    public function setRequest(S2Base_Request $request){
        $this->request = $request;
    }
    public final function putError($key,$val){
        self::$errors[$key] = $val;
    }
    public final function getError($key){
        if(isset(self::$errors[$key])){
            return self::$errors[$key];
        }
        return null;
    }
    public final function getErrors(){
        return self::$errors;
    }
    public final function setRendered($value = true){
        self::$rendered = $value;
    }
    public final function isRendered(){
        return self::$rendered;
    }
    public function process(){
        $this->actionTpl = $this->action->execute($this->request,$this);
        if ($this->actionTpl === null){
            $this->actionTpl = $this->getDefaultActionTpl();
        }
        if (!is_string($this->actionTpl)){
            throw new S2Base_RuntimeException('ERR108',array($this->actionTpl));
        }
        if (!$this->isRendered()){
            $this->view();
        }
    }
    public function view(){
        $mod = $this->request->getModule();
        $act = $this->request->getAction();
        $this->template_dir = S2BASE_PHP5_ROOT . '/app/modules';
        $this->assign('errors',self::$errors);
        $this->assign('mod_key',S2BASE_PHP5_REQUEST_MODULE_KEY);
        $this->assign('act_key',S2BASE_PHP5_REQUEST_ACTION_KEY);
        $this->assign('module',$mod);
        $this->assign('action',$act);
        $this->assign('request',$this->request);
        $this->assign('module_view_dir',S2BASE_PHP5_ROOT . "/app/modules/$mod/view");
        $this->assign('commons_view_dir',S2BASE_PHP5_ROOT . '/app/commons/view');
        if (preg_match("/^redirect:(.+)$/",$this->actionTpl,$matches)){
            $this->redirect($matches[1]);
            return;
        } else if (preg_match("/^file:/",$this->actionTpl)){
            $viewFile = $this->actionTpl;
        } else {
            $viewFile = "$mod/view/" . $this->actionTpl;
            if (!file_exists($this->template_dir . '/' . $viewFile)) {
                throw new S2Base_RuntimeException('ERR109',
                    array($mod, $act, $this->template_dir . '/' . $viewFile));
            }
        }
        if($this->layout == null){
            $this->display($viewFile);
        }else{
            $this->assign('content_for_layout',$viewFile);
            $this->display($this->layout);
        }
        $this->setRendered(true);        
    }
    protected function getDefaultActionTpl(){
        return $this->request->getAction() . self::TPL_SUFFIX;
    }
    private function redirect($target){
        $targets = explode(':',$target);
        if (count($targets) == 2){
            $this->request->setModule($targets[0]);
            $this->request->setAction($targets[1]);
        }else if(count($targets) == 1) {
            $this->request->setAction($targets[0]);
        }else{
            throw new S2Base_RuntimeException('ERR106',array($target));
        }
        S2Base_Dispatcher::dispatch($this->request);
        return;
    }
}

class S2Base_SimpleAction implements S2Base_Action {
    public function execute(S2Base_Request $request,S2Base_View $view) {}
}

class S2Base_Dispatcher {
    public static $controller = 'S2Base_SmartyController';
    private static $redirects = array();
    public static function dispatch(S2Base_Request $request) {
        self::initialize($request);
        $action = self::instantiateAction($request);
        $controller = self::instantiateController();
        $controller->setAction($action);
        $controller->setRequest($request);
        $controller->process();
    }
    public static function initialize(S2Base_Request $request){
        $mod = $request->getModule();
        $act = $request->getAction();
        self::pushRedirect($mod . ":" . $act);
        $actClassName = ucfirst($act) . "Action";
        if(!is_dir(S2BASE_PHP5_ROOT . "/app/modules/$mod")){
            throw new S2Base_RuntimeException('ERR103',array($mod));
        }
        self::requireIfExists(S2BASE_PHP5_ROOT . "/app/modules/$mod/$mod.inc.php");
        self::requireIfExists(S2BASE_PHP5_ROOT . "/app/modules/$mod/action/$actClassName.inc.php");
    }
    public static function instantiateAction(S2Base_Request $request) {
        $mod = $request->getModule();
        $act = $request->getAction();
        $actClassName = ucfirst($act) . "Action";
        $actClassFile = S2BASE_PHP5_ROOT . 
            "/app/modules/$mod/action/$actClassName.class.php";
        if(!is_readable($actClassFile)){
           return new S2Base_SimpleAction();
        }
        require_once($actClassFile);
        return self::getActionWithS2Container($mod,$act,$actClassName);
    }
    protected static function instantiateController(){
        $controllerClass = self::$controller;
        return new $controllerClass();
    }
    private static function getActionWithS2Container($mod,$act,$actClassName){
        $dicon = S2BASE_PHP5_ROOT . 
                 "/app/modules/$mod/dicon/$actClassName" .
                 S2BASE_PHP5_DICON_SUFFIX;
        if(!is_readable($dicon)){
            return new $actClassName();
        }
        $container = S2ContainerFactory::create($dicon);
        $container->init();
        $componentKey = null;
        if ($container->hasComponentDef($act)){
            $componentKey = $act;
        }else if ($container->hasComponentDef($actClassName)){
            $componentKey = $actClassName;
        }
        if($componentKey != null){
            return $container->getComponent($componentKey);
        }
        $cd = new S2Container_ComponentDefImpl($actClassName);
        $container->register($cd);
        return $cd->getComponent();
    }
    private static function requireIfExists($file){
        if(is_readable($file)){
            require_once($file);
        }
    }
    private static function pushRedirect($target){
        if (in_array($target,self::$redirects)){
            throw new S2Base_RuntimeException('ERR107',array($target,self::$redirects));
        }else{
            self::$redirects[] = $target;
        }
    }
}

abstract class S2Base_AbstractFilterInterceptor 
    extends S2Container_AbstractInterceptor
    implements S2Base_FilterInterceptor{
    protected $invocation;
    protected $request;
    protected $moduleName;
    protected $actionName;
    protected $action;
    protected $view;
    protected $controller;
    public function invoke(S2Container_MethodInvocation $invocation) {
        $this->init($invocation);
        $beforeResult = $this->before();
        if($beforeResult != null){
            return $beforeResult;
        }
        return $this->after($invocation->proceed());
    }    
    private function init($invocation){
        $this->invocation = $invocation;
        $this->action = $invocation->getThis();
        $methodName = $invocation->getMethod()->getName();
        $this->request = null;
        $this->view = null;
        $this->moduleName = null;
        $this->actionName = null;
        if ($this->action instanceof S2Base_Action and
            $methodName == 'execute'){
            $args = $invocation->getArguments();
            $this->request = $args[0];
            if ($this->request instanceof S2Base_Request){
                $this->moduleName  = $this->request->getModule();
                $this->actionName  = $this->request->getAction();
            }
            $this->view = $args[1];
            $this->controller = $this->view;
        }
    }
}

abstract class S2Base_AbstractAfterFilter 
    extends S2Base_AbstractFilterInterceptor {
    public function before(){
        return;   
    }
}

abstract class S2Base_AbstractBeforeFilter 
    extends S2Base_AbstractFilterInterceptor {
    public function after($viewName){
        return $viewName;   
    }
}

abstract class S2Base_AbstractValidateFilter 
    extends S2Base_AbstractBeforeFilter {
    const VALIDATE_DIR = '/validate/';
    protected $rule;
    abstract public function validate();
    abstract public function getSuffix();
    public function before(){
        if($this->rule == null){
            $ruleFile = S2BASE_PHP5_ROOT
                      . '/app/modules/' 
                      . $this->moduleName
                      . self::VALIDATE_DIR
                      . $this->actionName
                      . '.'
                      . $this->getSuffix()
                      . '.ini';
            if(is_readable($ruleFile)){
                $this->rule = parse_ini_file($ruleFile,true);
            }
        }
        return $this->validate();   
    }
}

?>
