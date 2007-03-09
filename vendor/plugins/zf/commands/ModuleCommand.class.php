<?php
class ModuleCommand implements S2Base_GenerateCommand {
    const MODEL_DIR = 'model';
    const VALIDATE_DIR = 'validate';

    protected $moduleName;
    protected $srcModuleDir;
    protected $srcCtlDir;
    protected $testModuleDir;
    protected $testCtlDir;
    protected $controllerName;
    protected $controllerClassName;
    protected $controllerClassFile;
    protected $dispatcher;
    protected $ctlServiceInterfaceName;

    public function __construct(){
        require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfDispatcher.php';
        $this->dispatcher = new S2Base_ZfDispatcher();
    }

    public static function getCtlServiceInterfaceName($moduleName) {
        $dispatcher = new S2Base_ZfDispatcher();
        return $dispatcher->formatName($moduleName, false) . 'Service';
    }

    public static function getActionControllerName($moduleName){
        $controllers = self::getAllControllers($moduleName);
        if(count($controllers) == 0){
            throw new Exception("Controller not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($controllers,'Controller list');
    }

    public static function getAllControllers($moduleName){
        $moduleDir = S2BASE_PHP5_MODULES_DIR . $moduleName;
        if (!is_dir($moduleDir)) {
            throw new Exception("dir not exists : [ $moduleDir ]");
        }

        $entries = scandir($moduleDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $moduleDir ]");
        }

        $controllers = array();
        foreach($entries as $entry) {
            $path = S2BASE_PHP5_MODULES_DIR . $moduleName . S2BASE_PHP5_DS . $entry;
            if(!preg_match("/^\./",$entry) and is_dir($path)){
                array_push($controllers,$entry);
            }
        }
        return $controllers;
    }
    
    public function getName(){
        return "module & controller";
    }
    
    public function execute(){
        try{
            $this->moduleName = $this->getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->validate($this->moduleName);
            $this->moduleName = self::formatModuleName($this->moduleName);
            $this->controllerName = S2Base_StdinManager::getValue('controller name ? : ');
            $this->validate($this->controllerName);
            list($this->controllerName, $this->controllerClassName, $this->controllerClassFile) = 
                self::getControllerNames($this->dispatcher, $this->moduleName, $this->controllerName);
            //$this->validate($this->controllerClassName);
            $this->ctlServiceInterfaceName = self::getCtlServiceInterfaceName($this->controllerName);
            if (!$this->finalConfirm()){
                return;
            }
            $this->createDirectory();
            $this->prepareFiles();
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    public static function getControllerNames($dispatcher, $moduleName, $controllerName) {
        $controllerName = self::formatModuleName($controllerName);
        $controllerClassName = $dispatcher->formatControllerName($controllerName);
        $controllerClassFile = $controllerClassName;
        if ($moduleName != S2BASE_PHP5_ZF_DEFAULT_MODULE) {
            $controllerClassName = $dispatcher->formatModuleName($moduleName)
                                 . '_' . $controllerClassName;
        }
        return array($controllerName, $controllerClassName, $controllerClassFile);
    }
    
    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid module name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name               : {$this->moduleName}" . PHP_EOL;
        print "  controller name           : {$this->controllerName}" . PHP_EOL;
        print "  controller class name     : {$this->controllerClassName}" . PHP_EOL;
        print "  controller interface name : {$this->ctlServiceInterfaceName}" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    public function createDirectory(){
        $this->srcModuleDir = S2BASE_PHP5_MODULES_DIR
                            . $this->moduleName
                            . S2BASE_PHP5_DS; 
        $this->srcCtlDir = $this->srcModuleDir
                         . $this->controllerName
                         . S2BASE_PHP5_DS; 
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR
                             . $this->moduleName
                             . S2BASE_PHP5_DS; 
        $this->testCtlDir = $this->testModuleDir
                          . $this->controllerName
                          . S2BASE_PHP5_DS; 
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_DICON_DIR,
            S2BASE_PHP5_ENTITY_DIR,
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_SERVICE_DIR,
            S2BASE_PHP5_VIEW_DIR,
            S2BASE_PHP5_DS . self::MODEL_DIR,
            S2BASE_PHP5_DS . self::VALIDATE_DIR);
        S2Base_CommandUtil::createDirectory($this->srcModuleDir);
        S2Base_CommandUtil::createDirectory($this->srcCtlDir);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->srcCtlDir. $dir);
        }

        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_SERVICE_DIR,
            S2BASE_PHP5_DS . self::MODEL_DIR);
        S2Base_CommandUtil::createDirectory($this->testModuleDir);
        S2Base_CommandUtil::createDirectory($this->testCtlDir);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->testCtlDir. $dir);
        }
    }

    public function prepareFiles(){
        $this->prepareActionControllerClassFile();
        $this->prepareModuleServiceInterfaceFile();
        $this->prepareModuleIncFile();
        $this->prepareIndexFile();
    }

    public function prepareActionControllerClassFile(){
        $srcFile = $this->srcModuleDir
                 . $this->controllerClassFile
                 . S2BASE_PHP5_CLASS_SUFFIX; 

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/module/controller.tpl");
        $keys = array("/@@CONTROLLER_CLASS_NAME@@/",
                      "/@@SERVICE_CLASS_NAME@@/",
                      "/@@CONTROLLER_NAME@@/",
                      "/@@TEMPLATE_NAME@@/");
        $reps = array($this->controllerClassName,
                      $this->ctlServiceInterfaceName,
                      $this->controllerName,
                      'index' . S2BASE_PHP5_ZF_TPL_SUFFIX);
        $tempContent = preg_replace($keys, $reps, $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public function prepareModuleServiceInterfaceFile(){
        $srcFile = $this->srcCtlDir
                 . 'service/'
                 . $this->ctlServiceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX; 

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/module/service.tpl");
        $keys = array("/@@SERVICE_CLASS_NAME@@/");
        $reps = array($this->ctlServiceInterfaceName);
        $tempContent = preg_replace($keys, $reps, $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public function prepareModuleIncFile(){
        $srcFile = $this->srcCtlDir
                 . "{$this->controllerName}.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/module/include.tpl');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public function prepareIndexFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_VIEW_DIR
                 . 'index'
                 . S2BASE_PHP5_ZF_TPL_SUFFIX; 

        $htmlFile = 'index.tpl';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/module/$htmlFile");

        $keys = array("/@@MODULE_NAME@@/",
                      "/@@CONTROLLER_NAME@@/");
        $reps = array($this->moduleName,
                      $this->controllerName);
        $tempContent = preg_replace($keys, $reps, $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public static function formatModuleName($name){
        if (trim($name) == '') {
            throw new Exception('invalid name. [empty]');
        }
        $name = preg_replace("/^_+/","",$name);
        $name = preg_replace("/_+$/","",$name);
        if (preg_match("/_/",$name)){
            $words = explode('_', $name);
            $fw = array_shift($words);
            $name = implode(' ', $words);
            $name = ucwords($name);
            $name = preg_replace("/\s/", '', $name);
            $name = $fw . $name;
        }
        return $name;
    }

    private function getModuleName() {
        $createLabel = '(new module)';

        $modules = S2Base_CommandUtil::getAllModules();

        if (count($modules) == 0) {
            array_unshift($modules, $createLabel, S2BASE_PHP5_ZF_DEFAULT_MODULE);
        } else {
            array_unshift($modules, $createLabel);
        }
        
        $result = S2Base_StdinManager::getValueFromArray($modules,"Module list");
        if ($result == $createLabel) {
            return S2Base_StdinManager::getValue('module name ? : ');
        }
        return $result;
    }

    public function setModuleName($moduleName) {
        $this->moduleName = $moduleName;
    }

    public function getSrcModuleDir() {
        return $this->srcModuleDir;
    }
    public function setSrcModuleDir($srcModuleDir) {
        $this->srcModuleDir = $srcModuleDir;
    }

    public function getSrcCtlDir() {
        return $this->srcCtlDir;
    }
    public function setSrcCtlDir($srcCtlDir) {
        $this->srcCtlDir = $srcCtlDir;
    }

    public function getTestModuleDir() {
        return $this->testModuleDir;
    }
    public function setTestModuleDir($testModuleDir) {
        $this->testModuleDir = $testModuleDir;
    }

    public function getTestCtlDir() {
        return $this->testCtlDir;
    }
    public function setTestCtlDir($testCtlDir) {
        $this->testCtlDir = $testCtlDir;
    }

    public function getControllerName() {
        return $this->controllerName;
    }
    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    public function getControllerClassName() {
        return $this->controllerClassName;
    }
    public function setControllerClassName($controllerClassName) {
        $this->controllerClassName = $controllerClassName;
    }

    public function getControllerClassFile() {
        return $this->controllerClassFile;
    }
    public function setControllerClassFile($controllerClassFile) {
        $this->controllerClassFile = $controllerClassFile;
    }

    public function getDispatcher() {
        return $this->dispatcher;
    }
    public function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function setCtlServiceInterfaceName($ctlServiceInterfaceName) {
        $this->ctlServiceInterfaceName = $ctlServiceInterfaceName;
    }

}
?>
