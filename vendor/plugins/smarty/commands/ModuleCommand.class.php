<?php
class ModuleCommand implements S2Base_GenerateCommand {
    protected $moduleName;
    protected $srcDirectory;
    protected $testDirectory;

    public function getName(){
        return "module";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_StdinManager::getValue('module name ? : ');
            $this->validate($this->moduleName);
            $this->srcDirectory = S2BASE_PHP5_MODULES_DIR . $this->moduleName;
            $this->testDirectory = S2BASE_PHP5_TEST_MODULES_DIR . $this->moduleName;
            if (!$this->finalConfirm()){
                return;
            }
            $this->createDirectory();
            $this->prepareFiles();
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid module name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name : {$this->moduleName}" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function createDirectory(){
        $dirs = array(
            S2BASE_PHP5_ACTION_DIR,
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_DICON_DIR,
            S2BASE_PHP5_ENTITY_DIR,
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_SERVICE_DIR,
            S2BASE_PHP5_VIEW_DIR);
        S2Base_CommandUtil::createDirectory($this->srcDirectory);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->srcDirectory. $dir);
        }

        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_SERVICE_DIR);
        S2Base_CommandUtil::createDirectory($this->testDirectory);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->testDirectory. $dir);
        }
    }

    protected function prepareFiles(){
        $this->prepareModuleIncFile();
        $this->prepareIndexFile();
    }

    protected function prepareModuleIncFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . S2BASE_PHP5_DS .
                   "{$this->moduleName}.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/module/include.php');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareIndexFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                     $this->moduleName . 
                     S2BASE_PHP5_VIEW_DIR . 
                     "index" .
                     S2BASE_PHP5_SMARTY_TPL_SUFFIX; 

        $htmlFile = defined('S2BASE_PHP5_LAYOUT') ? 'index_layout.php' : 'index.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/module/$htmlFile");
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                    $this->moduleName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
