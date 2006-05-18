<?php
class DiconCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $diconName;
    
    public function getName(){
        return "dicon";
    }

    public function execute(){
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $this->diconName = S2Base_StdinManager::getValue('dicon name ? : ');
        $this->validate($this->diconName);
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
        $types = array('yes','no');
        $rep = S2Base_StdinManager::getValueFromArray($types,
                                        "confirmation");
        if ($rep == S2Base_StdinManager::EXIT_LABEL or 
            $rep == 'no'){
            return false;
        }
        return true;
    }

    private function prepareFiles(){
        $this->prepareDiconFile();
    }
    
    private function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->diconName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'dicon.php');
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>