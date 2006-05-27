<?php
require_once('MapleCommandUtil.class.php');
class ActionDiconCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $actionPath;
    private $diconPath;

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
        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name : {$this->moduleName} \n";
        print "  action path : {$this->actionPath} \n";
        print "  dicon path  : {$this->diconPath} \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function prepareFiles(){
        $this->prepareDiconFile();
    }
    
    private function prepareDiconFile(){
        $srcFile = MODULE_DIR . 
                   $this->diconPath;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_MAPLE .
                                                 '/skeleton/dicon.php');
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>