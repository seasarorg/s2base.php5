<?php
class ModuleCommand implements S2Base_GenerateCommand {
    private $moduleName;
    private $srcDirectory;
    private $testDirectory;

    public function getName(){
        return "module";
    }

    public function execute(){
        $modName = S2Base_StdinManager::getValue('module name ? : ');

        try{
            $this->validate($modName);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        $this->moduleName = $modName;
        $this->srcDirectory = S2BASE_PHP5_MODULES_DIR . $modName;
        $this->testDirectory = S2BASE_PHP5_TEST_MODULES_DIR . $modName;
        if (!$this->finalConfirm()){
            return;
        }
        $this->createDirectory();
        $this->prepareFiles();
    }

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid module name. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name : {$this->moduleName} \n";

        return S2Base_StdinManager::isYes('ok ?');
/*
        $types = array('yes','no');
        $rep = S2Base_StdinManager::getValueFromArray($types,
                                        "confirmation");
        if ($rep == S2Base_StdinManager::EXIT_LABEL or 
            $rep == 'no'){
            return false;
        }
        return true;
*/
    }

    private function createDirectory(){
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_DICON_DIR,
            S2BASE_PHP5_ENTITY_DIR,
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_SERVICE_DIR);

        $this->createDirectoryInternal($this->srcDirectory);

        foreach($dirs as $dir){
            $this->createDirectoryInternal($this->srcDirectory. $dir);
        }

        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_SERVICE_DIR);

        $this->createDirectoryInternal($this->testDirectory);

        foreach($dirs as $dir){
            $this->createDirectoryInternal($this->testDirectory. $dir);
        }
    }

    private function createDirectoryInternal($path){
        if(S2Base_CommandUtil::createDirectory($path)){
            print "[INFO ] create : $path\n";
        }else{
            print "[INFO ] exists : $path\n";
        }
    }


    private function prepareFiles(){
        $this->prepareModuleIncFile();
    }

    private function prepareModuleIncFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . S2BASE_PHP5_DS .
                   "{$this->moduleName}.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'module_inc.php');
        CmdCommand::writeFile($srcFile,$tempContent);
    }

}
?>
