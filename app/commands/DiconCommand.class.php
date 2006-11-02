<?php
class DiconCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $diconName;
    
    public function getName(){
        return "dicon";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->diconName = S2Base_StdinManager::getValue('dicon name ? : ');
            $this->validate($this->diconName);
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid dicon name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name     : {$this->moduleName}" . PHP_EOL;
        print "  dicon file name : {$this->diconName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareDiconFile();
    }
    
    protected function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->diconName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dicon/dicon.php');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>