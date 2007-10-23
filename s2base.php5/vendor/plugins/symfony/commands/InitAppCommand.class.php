<?php
class InitAppCommand implements S2Base_GenerateCommand
{
    private $appName;
    
    public function getName ()
    {
        return "init-app";
    }
    
    public function execute ()
    {
        
        $this->appName = S2Base_StdinManager::getValue('application name ? : ');
        if(S2Base_CommandUtil::isListExitLabel($this->appName)){
            return;
        }
        
        if (!$this->finalConfirm()){
            return;
        }
        
        S2Base_SymfonyCommandUtil::execSfCmd('init-app', $this->appName, S2BASE_PHP5_ROOT);
        $this->prepareProject();
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
        return S2Base_StdinManager::isYes('confirm ?');
    }
    
    private function prepareProject ()
    {
        S2Base_SymfonyCommandUtil::setAppName($this->appName);
        $this->copyS2BaseFrontWebController();
        $this->copyFactoriesYml();
        $this->createDirectories();
        $this->createTestDirectory();
        $this->copyTestHelper();
    }
    
    private function copyS2BaseFrontWebController ()
    {
        $fcName = "S2Base_FrontWebController.class.php";
        $srcFile = S2BASE_PHP5_ROOT . S2BASE_PHP5_DS .
                   "apps"           . S2BASE_PHP5_DS .
                   $this->appName   . S2BASE_PHP5_DS .
                   "lib"            . S2BASE_PHP5_DS .
                   $fcName;
        @unlink($srcFile);
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SF
                     . '/skeletons/controller/' . $fcName);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
    
    private function copyFactoriesYml()
    {
        $ymlName = "factories.yml";
        $srcFile = S2BASE_PHP5_ROOT . S2BASE_PHP5_DS .
                   "apps"           . S2BASE_PHP5_DS .
                   $this->appName   . S2BASE_PHP5_DS .
                   "config"         . S2BASE_PHP5_DS .
                   $ymlName;
        @unlink($srcFile);
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SF
                     . '/skeletons/config/' . $ymlName);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
    
    private function createDirectories()
    {
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_ENTITY_DIR,);
        foreach ($dirs as $dir) {
            S2Base_CommandUtil::createDirectory(
                S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'lib' . $dir
            );
        }
    }
    
    private function createTestDirectory()
    {
        $testDir = S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'test' .
                        S2BASE_PHP5_DS . 'unit' . S2BASE_PHP5_DAO_DIR;
        S2Base_CommandUtil::createDirectory($testDir);
    }
    
    private function copyTestHelper()
    {
        $helperName = "TestHelper.php";
        $testDir = S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'test' .
                        S2BASE_PHP5_DS . 'unit';
        $srcFile = S2BASE_PHP5_ROOT
                 . S2BASE_PHP5_DS
                 . "test"
                 . S2BASE_PHP5_DS
                 . "unit"
                 . S2BASE_PHP5_DS
                 . $helperName;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SF
                     . '/skeletons/config/' . $helperName);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
