<?php
class ServiceCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $controllerName;
    protected $serviceInterfaceName;
    protected $serviceClassName;
    protected $moduleServiceInterfaceName;
    protected $srcModuleDir;
    protected $srcCtlDir;
    protected $testModuleDir;
    protected $testCtlDir;

    public function getName(){
        return "service";
    }

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
            $this->moduleServiceInterfaceName = ModuleCommand::getCtlServiceInterfaceName($this->controllerName);
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
        print "  controller name           : {$this->controllerName}" . PHP_EOL;
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
        $this->srcModuleDir  = S2BASE_PHP5_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->srcCtlDir     = $this->srcModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;
        $this->prepareServiceImplFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDiconFile();
    }
    
    protected function prepareServiceImplFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/service/service.tpl');

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
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/service/interface.tpl');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = $this->testCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/service/test.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@SERVICE_INTERFACE@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->controllerName,
                              $this->serviceInterfaceName,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDiconFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/service/dicon.tpl');
        $tempContent = preg_replace("/@@SERVICE_CLASS@@/",
                                    $this->serviceClassName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
