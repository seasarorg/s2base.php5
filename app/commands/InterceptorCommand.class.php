<?php
class InterceptorCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $interceptorClassName;
    
    public function getName(){
        return "interceptor";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $this->interceptorClassName = S2Base_StdinManager::getValue('interceptor class name ? : ');
        try{
            $this->validate($this->interceptorClassName);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }        

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid interceptor name. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name            : {$this->moduleName} \n";
        print "  interceptor class name : {$this->interceptorClassName} \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }
    
    private function prepareFiles(){
        $this->prepareInterceptorFile();
    }
    
    private function prepareInterceptorFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_INTERCEPTOR_DIR . 
                   "{$this->interceptorClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 "interceptor_default.php");
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->interceptorClassName,
                             $tempContent);   
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>