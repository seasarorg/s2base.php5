<?php
require_once('DefaultCommandUtil.class.php');
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
            DefaultCommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        DefaultCommandUtil::validate($name,"Invalid module name. [ $name ]");
    }

    protected function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name : {$this->moduleName} \n";

        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function createDirectory(){
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_DICON_DIR,
            S2BASE_PHP5_ENTITY_DIR,
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_SERVICE_DIR);
        DefaultCommandUtil::createDirectory($this->srcDirectory);
        foreach($dirs as $dir){
            DefaultCommandUtil::createDirectory($this->srcDirectory. $dir);
        }

        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_SERVICE_DIR);
        DefaultCommandUtil::createDirectory($this->testDirectory);
        foreach($dirs as $dir){
            DefaultCommandUtil::createDirectory($this->testDirectory. $dir);
        }
    }

    protected function prepareFiles(){
        $this->prepareModuleIncFile();
    }

    protected function prepareModuleIncFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . S2BASE_PHP5_DS .
                   "{$this->moduleName}.inc.php";
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'module' . S2BASE_PHP5_DS
                     . 'include.php');
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
