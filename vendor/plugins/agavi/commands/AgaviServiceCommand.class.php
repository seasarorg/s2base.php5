<?php
class AgaviServiceCommand implements S2Base_GenerateCommand
{
    private $pathName   = S2BASE_PHP5_AG_DEFAULT_PATH;
    private $moduleName = S2BASE_PHP5_AG_DEFAULT_MODULE;
    private $moduleDir;
    private $serviceInterfaceName;
    private $serviceClassName;
    
    public function getName ()
    {
        return "service";
    }

    public function execute ()
    {
        $pathName = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_PATH);
        if (strlen($pathName) > 0) {
            $this->pathName = $pathName;
        }
        $targetDir = $this->pathName . S2BASE_PHP5_AG_MODULE_DIR;
        $this->moduleName = AgaviCommandUtil::getModuleName($targetDir);
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        $this->moduleDir = $targetDir . S2BASE_PHP5_DS . $this->moduleName;
        
        $iface  = S2Base_StdinManager::getValue('Service Interface Name ? : ');
        $this->validate($iface);
        $this->serviceInterfaceName = $iface;
        $this->serviceClassName     = $iface . 'Impl';
        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name             : {$this->moduleName} \n";
        print "  service interface name  : {$this->serviceInterfaceName} \n";
        print "  service class name      : {$this->serviceClassName} \n";
        print "  service test class name : {$this->serviceClassName}Test \n";
        print "  service dicon file name : {$this->serviceInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid service name. [ $name ]");
    }

    private function prepareFiles(){
        $this->prepareServiceImplFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDiconFile();
    }

    private function prepareServiceImplFile(){
        $srcFile = $this->moduleDir .
                    S2BASE_PHP5_SERVICE_DIR .
                   "{$this->serviceClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                    'service/service.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->serviceClassName,$this->serviceInterfaceName);
        $tempContent = preg_replace($patterns, $replacements, $tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceInterfaceFile(){
        $srcFile = $this->moduleDir . 
                    S2BASE_PHP5_SERVICE_DIR .
                   "{$this->serviceInterfaceName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                    'service/interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                                    $this->serviceInterfaceName,
                                    $tempContent);   
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $testFile = $this->pathName .
                    S2BASE_PHP5_AG_TEST_DIR . 
                    $this->moduleName . 
                    S2BASE_PHP5_SERVICE_DIR . 
                    "$testName.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_AG_SKELETON_DIR .
                                                    'agavi_service_test.php');
        $patterns = array("/@@AG_PROJECT_DIR@@/","/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@SERVICE_INTERFACE@@/");
        $replacements = array($this->pathName,$testName,$this->moduleName,$this->serviceInterfaceName);
        $tempContent = preg_replace($patterns, $replacements, $tempContent);
        CmdCommand::writeFile($testFile,$tempContent);
    }

    private function prepareDiconFile(){
        $srcFile = $this->moduleDir . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->serviceInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                    'service/dicon.php');
        $tempContent = preg_replace("/@@SERVICE_CLASS@@/",
                                    $this->serviceClassName,
                                    $tempContent);   
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>
