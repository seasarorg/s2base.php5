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
        return "<agavi> service";
    }

    public function execute ()
    {
        $pathName = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_PATH);
        if (strlen($pathName) > 0)
        {
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
        $this->prepareFiles();
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
                                                    'service_impl.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                                    $this->serviceClassName,
                                    $tempContent);   
        $tempContent = preg_replace("/@@INTERFACE_NAME@@/",
                                    $this->serviceInterfaceName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }

    private function prepareServiceInterfaceFile(){
        $srcFile = $this->moduleDir . 
                    S2BASE_PHP5_SERVICE_DIR .
                   "{$this->serviceInterfaceName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                    'service_interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                                    $this->serviceInterfaceName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
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
        $tempContent = preg_replace("/@@AG_PROJECT_DIR@@/",
                                    $this->pathName,
                                    $tempContent); 
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                                    $testName,
                                    $tempContent);   
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                    $this->moduleName,
                                    $tempContent);
        $tempContent = preg_replace("/@@SERVICE_INTERFACE@@/",
                                    $this->serviceInterfaceName,
                                    $tempContent);
        S2Base_CommandUtil::writeFile($testFile,$tempContent);
        print "[INFO ] create : $testFile\n";
    }

    private function prepareDiconFile(){
        $srcFile = $this->moduleDir . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->serviceInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                    'service_dicon.php');
        $tempContent = preg_replace("/@@SERVICE_CLASS@@/",
                                    $this->serviceClassName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }
}
?>