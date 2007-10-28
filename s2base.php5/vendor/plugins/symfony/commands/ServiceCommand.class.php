<?php
class ServiceCommand implements S2Base_GenerateCommand {
    
    protected $appName;
    protected $moduleName;
    protected $serviceClassName;
    
    /**
     * @see S2Base_GenerateCommand::getName()
     */
    public function getName(){
        return "service";
    }

    /**
     * @see S2Base_GenerateCommand::isAvailable()
     */
    public function isAvailable(){
        return true;
    }

    /**
     * @see S2Base_GenerateCommand::execute()
     */
    public function execute(){
        $this->appName = S2Base_SymfonyCommandUtil::getAppName();
        if(S2Base_CommandUtil::isListExitLabel($this->appName)){
            return;
        }
        
        $this->moduleName = S2Base_SymfonyCommandUtil::getModuleName($this->appName);
        if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
            return;
        }

        $this->serviceClassName = S2Base_StdinManager::getValue('service class name ? : ');
        $this->validate($this->serviceClassName);

        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid service name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name             : {$this->moduleName}" . PHP_EOL;
        print "  service class name      : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name : {$this->serviceClassName}Test" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareServiceClassFile();
        $this->prepareServiceTestFile();
    }
    
    protected function prepareServiceClassFile(){
        $srcFile = S2BASE_PHP5_ROOT  . S2BASE_PHP5_DS .
                   "apps"            . S2BASE_PHP5_DS .
                   $this->appName    . S2BASE_PHP5_DS .
                   "modules"         . S2BASE_PHP5_DS .
                   $this->moduleName . 
                   S2BASE_PHP5_SERVICE_DIR .
                   S2BASE_PHP5_DS . $this->serviceClassName .
                   S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SF
                     . '/skeletons/service/service.tpl');

        $patterns = array('/@@CLASS_NAME@@/');
        $replacements = array($this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $testDir = S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'test' .
                        S2BASE_PHP5_DS . 'unit';
        $srcFile = $testDir 
                 . S2BASE_PHP5_DS
                 . $this->appName
                 . S2BASE_PHP5_DS
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . S2BASE_PHP5_DS . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SF
                     . '/skeletons/service/test.tpl');

        $patterns = array("/@@APP_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@CLASS_NAME@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($this->appName,
                              $this->moduleName,
                              $testName,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
