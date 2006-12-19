<?php
class ServiceCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $serviceInterfaceName;
    protected $serviceClassName;
    protected $moduleServiceInterfaceName;

    public function getName(){
        return "service";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }

            $this->moduleServiceInterfaceName = ModuleCommand::getModuleServiceInterfaceName($this->moduleName);
            $this->serviceInterfaceName = S2Base_StdinManager::getValue('service interface name ? : ');
            $this->validate($this->serviceInterfaceName);

            $this->isImplementsModuleService = S2Base_StdinManager::isYes("implements {$this->moduleServiceInterfaceName} ?");

            $this->serviceClassName = $this->serviceInterfaceName . "Impl";
            $serviceClassNameTmp = S2Base_StdinManager::getValue("service class name ? [{$this->serviceClassName}] : ");
            if(trim($serviceClassNameTmp) != ''){
                $this->serviceClassName = $serviceClassNameTmp;
            }
            $this->validate($this->serviceClassName);

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
        S2Base_CommandUtil::validate($name,"Invalid service name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name               : {$this->moduleName}" . PHP_EOL;
        print "  service interface name    : {$this->serviceInterfaceName}" . PHP_EOL;
        print $this->isImplementsModuleService ?
              "  implements module service : Yes ({$this->moduleServiceInterfaceName})" . PHP_EOL :
              "  implements module service : No" . PHP_EOL;
        print "  service class name        : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name   : {$this->serviceClassName}Test" . PHP_EOL;
        print "  service dicon file name   : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareServiceImplFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDiconFile();
    }
    
    protected function prepareServiceImplFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
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
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'service/interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'service/test.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@SERVICE_INTERFACE@@/","/@@SERVICE_CLASS@@/");
        $replacements = array($testName,$this->moduleName,$this->serviceInterfaceName,$this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'service/dicon.php');
        $tempContent = preg_replace("/@@SERVICE_CLASS@@/",
                                    $this->serviceClassName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
