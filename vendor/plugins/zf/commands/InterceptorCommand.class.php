<?php
/**
 * interceptorを生成します。
 * 
 * 生成ファイル
 * <ul>
 *   <li>app/modules/module名/interceptor/interceptor名.class.php</li>
 * </ul>
 * 
 */
class InterceptorCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $controllerName;
    protected $interceptorClassName;

    protected $srcModuleDir;
    protected $srcCtlDir;
    protected $testModuleDir;
    protected $testCtlDir;

    /**
     * @see S2Base_GenerateCommand::getName()
     */    
    public function getName(){
        return "interceptor";
    }

    /**
     * @see S2Base_GenerateCommand::execute()
     */
    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->controllerName = ModuleCommand::getActionControllerName($this->moduleName);
            if(S2Base_CommandUtil::isListExitLabel($this->controllerName)){
                return;
            }
            $this->interceptorClassName = S2Base_StdinManager::getValue('interceptor class name ? : ');
            $this->validate($this->interceptorClassName);
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid interceptor name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name            : {$this->moduleName}" . PHP_EOL;
        print "  controoler name        : {$this->controllerName}" . PHP_EOL;
        print "  interceptor class name : {$this->interceptorClassName}" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }
    
    protected function prepareFiles(){
        $this->srcModuleDir  = S2BASE_PHP5_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->srcCtlDir     = $this->srcModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;

        $this->prepareInterceptorFile();
    }

    protected function prepareInterceptorFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_INTERCEPTOR_DIR
                 . $this->interceptorClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/interceptor/default.tpl');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->interceptorClassName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
