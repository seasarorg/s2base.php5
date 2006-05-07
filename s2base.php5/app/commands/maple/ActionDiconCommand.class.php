<?php
require_once('MapleCommandUtil.class.php');
class ActionDiconCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $actionPath;
    
    public function getName(){
        return "action dicon";
    }

    public function execute(){
        $this->moduleName = MapleCommandUtil::getModuleName();
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        $this->actionPath = MapleCommandUtil::getActionPath($this->moduleName);
        if($this->actionPath == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        $this->diconPath = preg_replace("/\.class\.php$/",S2BASE_PHP5_DICON_SUFFIX,$this->actionPath);
        $this->prepareFiles();
    }

    private function prepareFiles(){
        $this->prepareDiconFile();
    }
    
    private function prepareDiconFile(){
        $srcFile = MODULE_DIR . 
                   $this->diconPath;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'maple/dicon.php');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }
}
?>