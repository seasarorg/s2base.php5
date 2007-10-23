<?php
class InitModuleCommand implements S2Base_GenerateCommand
{
    private $appName;
    private $moduleName;
    
    public function getName ()
    {
        return "init-module";
    }
    
    public function execute ()
    {
        $this->appName = S2Base_SymfonyCommandUtil::getAppName();
        if(S2Base_CommandUtil::isListExitLabel($this->appName)){
            return;
        }
        $this->moduleName = S2Base_StdinManager::getValue('module name ? : ');
        $this->validate($this->moduleName);
        if (!$this->finalConfirm()){
            return;
        }
        
        $cmd = $this->appName .' '.$this->moduleName;
        S2Base_SymfonyCommandUtil::execSfCmd('init-module', $cmd, S2BASE_PHP5_ROOT);
        $this->prepareFiles();
    }
    
    public function isAvailable(){
        return true;
    }
    
    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    private function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  application name   : {$this->appName}". PHP_EOL;
        print "  module name        : {$this->moduleName}". PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }
    
    private function prepareFiles ()
    {
        $this->createDirectories();
        $this->createTestDirectory();
        $this->prepareModuleAutoloadYmlFile();
    }
    
    private function createDirectories()
    {
        $modulePath = S2BASE_PHP5_ROOT
                    . S2BASE_PHP5_DS
                    . "apps"
                    . S2BASE_PHP5_DS
                    . $this->appName
                    . S2BASE_PHP5_DS
                    . "modules"
                    . S2BASE_PHP5_DS
                    . $this->moduleName;
        $dirs = array(
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_SERVICE_DIR);
        foreach ($dirs as $dir) {
            S2Base_CommandUtil::createDirectory($modulePath . $dir);
        }
    }
    
    private function createTestDirectory()
    {
        $testDir = S2BASE_PHP5_ROOT
                 . S2BASE_PHP5_DS
                 . 'test'
                 . S2BASE_PHP5_DS
                 . 'unit'
                 . S2BASE_PHP5_DS
                 . $this->appName
                 . S2BASE_PHP5_DS
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR;
        S2Base_CommandUtil::createDirectory($testDir, 0755, true);
    }
    
    private function prepareModuleAutoloadYmlFile ()
    {
        $srcFile = S2BASE_PHP5_ROOT  . S2BASE_PHP5_DS .
                   "apps"            . S2BASE_PHP5_DS .
                   $this->appName    . S2BASE_PHP5_DS .
                   "config"          . S2BASE_PHP5_DS .
                   "autoload.yml";

        $tempContent = S2Base_CommandUtil::readFile(
            S2BASE_PHP5_PLUGIN_SF . '/skeletons/config/module_autoload.yml'
        );

        $tempContent = preg_replace(
            "/@@MODULE_NAME@@/",
            $this->moduleName,
            $tempContent
        );
        if (file_exists($srcFile)) {
            $tempContent = preg_replace("/autoload:/", '', $tempContent);
            file_put_contents($srcFile, $tempContent, FILE_APPEND);
        } else {
            S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        }
    }
}
?>
