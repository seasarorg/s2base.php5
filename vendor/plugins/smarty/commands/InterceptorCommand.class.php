<?php
class InterceptorCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $interceptorClassName;
    protected $type;

    public function getName(){
        return "interceptor";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $types = array('default','arround','before','after','validate');
            $this->type = S2Base_StdinManager::getValueFromArray($types,
                                            "Type list");
            if(S2Base_CommandUtil::isListExitLabel($this->type)){
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
        print "  type                   : {$this->type}" . PHP_EOL;
        print "  interceptor class name : {$this->interceptorClassName}" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareInterceptorFile();
    }
    
    protected function prepareInterceptorFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_INTERCEPTOR_DIR
                 . $this->interceptorClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/interceptor/{$this->type}.php");
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->interceptorClassName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
