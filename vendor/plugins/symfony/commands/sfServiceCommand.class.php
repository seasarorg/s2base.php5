<?php
class sfServiceCommand implements S2Base_GenerateCommand
{
    private $pathName   = S2BASE_PHP5_SF_DEFAULT_PATH;
    private $appName;
    private $moduleName;
    private $moduleServiceInterfaceName;
    private $serviceInterfaceName;
    private $serviceClassName;
    
    public function getName(){
        return "service";
    }

    public function execute(){
        $pathName = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_PATH);
        if (strlen($pathName) > 0) {
            $this->pathName = $pathName;
        }
        $appDir = $this->pathName . S2BASE_PHP5_DS . 'apps';
        try{
            $this->appName = sfCommandUtil::getAppName($appDir);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        if($this->appName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $moduleDir = $appDir . S2BASE_PHP5_DS .
                     $this->appName . S2BASE_PHP5_DS . 'modules';
        try{
            $this->moduleName = sfCommandUtil::getModuleName($moduleDir);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $this->serviceInterfaceName = S2Base_StdinManager::getValue('service interface name ? : ');
        try{
            $this->validate($this->serviceInterfaceName);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        
        $this->moduleServiceInterfaceName = ucfirst($this->moduleName . 'Service');
        $this->isImplementsModuleService = S2Base_StdinManager::isYes("implements {$this->moduleServiceInterfaceName} ?");
        
        $this->serviceClassName = $this->serviceInterfaceName . "Impl";
        $serviceClassNameTmp = S2Base_StdinManager::getValue("service class name ? [{$this->serviceClassName}] : ");
        if(trim($serviceClassNameTmp) != ''){
            $this->serviceClassName = $serviceClassNameTmp;
        }
        try{
            $this->validate($this->serviceClassName);
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
        S2Base_CommandUtil::validate($name,"Invalid service name. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  project path            : {$this->pathName} \n";
        print "  application name        : {$this->appName} \n";
        print "  module name             : {$this->moduleName} \n";
        print "  service interface name  : {$this->serviceInterfaceName} \n";
        print $this->isImplementsModuleService ?
              "  implements module service : Yes ({$this->moduleServiceInterfaceName})" . PHP_EOL :
              "  implements module service : No" . PHP_EOL;
        print "  service class name      : {$this->serviceClassName} \n";
        print "  service test class name : {$this->serviceClassName}Test \n";
        print "  service dicon file name : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function prepareFiles(){
        $this->prepareServiceImplFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDiconFile();
    }
    
    private function prepareServiceImplFile(){
        $srcFile = $this->pathName   . S2BASE_PHP5_DS .
                   "apps"            . S2BASE_PHP5_DS .
                   $this->appName    . S2BASE_PHP5_DS .
                   "modules"         . S2BASE_PHP5_DS .
                   $this->moduleName .
                   S2BASE_PHP5_SERVICE_DIR . 
                   "{$this->serviceClassName}" .
                   S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'service/service.php');
        
        $implementsInterface = $this->serviceInterfaceName;                     
        if ($this->isImplementsModuleService) {
            if ($this->serviceInterfaceName != $this->moduleServiceInterfaceName) {
                $implementsInterface .= ', ' . $this->moduleServiceInterfaceName;
            }
        }
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->serviceClassName,$implementsInterface);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceInterfaceFile(){
        $srcFile = $this->pathName   . S2BASE_PHP5_DS .
                   "apps"            . S2BASE_PHP5_DS .
                   $this->appName    . S2BASE_PHP5_DS .
                   "modules"         . S2BASE_PHP5_DS .
                   $this->moduleName .
                   S2BASE_PHP5_SERVICE_DIR . 
                   "{$this->serviceInterfaceName}" .
                   S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'service/interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $testFile = $this->pathName     . S2BASE_PHP5_DS .
                    "test"              . S2BASE_PHP5_DS .
                    "unit"              . S2BASE_PHP5_DS .
                    $this->appName      . S2BASE_PHP5_DS .
                    $this->moduleName   .
                    S2BASE_PHP5_SERVICE_DIR . 
                    "$testName" .
                    S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SF_SKELETON_DIR
                     . 'sf_service_test.php');
        $patterns = array("/@@SF_ROOT_DIR@@/",
                          "/@@CLASS_NAME@@/",
                          "/@@APP_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@SERVICE_INTERFACE@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($this->pathName,$testName,$this->appName,$this->moduleName,$this->serviceInterfaceName,$this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($testFile,$tempContent);
    }

    private function prepareDiconFile(){
        $srcFile = $this->pathName   . S2BASE_PHP5_DS .
                   "apps"            . S2BASE_PHP5_DS .
                   $this->appName    . S2BASE_PHP5_DS .
                   "modules"         . S2BASE_PHP5_DS .
                   $this->moduleName .
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->serviceInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'service/dicon.php');
        $tempContent = preg_replace("/@@SERVICE_CLASS@@/",
                                    $this->serviceClassName,
                                    $tempContent);   
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>