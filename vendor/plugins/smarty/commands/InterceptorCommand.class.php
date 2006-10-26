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
            $this->moduleName = DefaultCommandUtil::getModuleName();
            if(DefaultCommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $types = array('default','arround','before','after','validate');
            $this->type = S2Base_StdinManager::getValueFromArray($types,
                                            "Type list");
            if(DefaultCommandUtil::isListExitLabel($this->type)){
                return;
            }

            $this->interceptorClassName = S2Base_StdinManager::getValue('interceptor class name ? : ');
            $this->validate($this->interceptorClassName);
 
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        } catch(Exception $e) {
            DefaultCommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        DefaultCommandUtil::validate($name,"Invalid interceptor name. [ $name ]");
    }

    protected function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name            : {$this->moduleName} \n";
        print "  type                   : {$this->type} \n";
        print "  interceptor class name : {$this->interceptorClassName} \n";
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
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/interceptor/{$this->type}.php");
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->interceptorClassName,
                             $tempContent);   
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
