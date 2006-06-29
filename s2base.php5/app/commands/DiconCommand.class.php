<?php
class DiconCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $diconName;
    
    public function getName(){
        return "dicon";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $this->diconName = S2Base_StdinManager::getValue('dicon name ? : ');
        try{
            $this->validate($this->diconName);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }        

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid dicon name. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name     : {$this->moduleName} \n";
        print "  dicon file name : {$this->diconName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function prepareFiles(){
        $this->prepareDiconFile();
    }
    
    private function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->diconName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dicon/dicon.php');
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>