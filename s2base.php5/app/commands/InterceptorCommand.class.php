<?php
class InterceptorCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $interceptorName;
    
    public function getName(){
        return "interceptor";
    }

    public function execute(){
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $this->interceptorName = S2Base_StdinManager::getValue('interceptor class name ? : ');
        $this->validate($this->interceptorName);
        $this->prepareFiles();
    }        

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid interceptor name. [ $name ]");
    }
    
    private function prepareFiles(){
        $this->prepareInterceptorFile();
    }
    
    private function prepareInterceptorFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_INTERCEPTOR_DIR . 
                   "{$this->interceptorName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 "interceptor_default.php");
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->interceptorName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }
}
?>