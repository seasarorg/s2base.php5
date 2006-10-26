<?php
require_once('DefaultCommandUtil.class.php');
class DiconCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $diconName;
    
    public function getName(){
        return "dicon";
    }

    public function execute(){
        try{
            $this->moduleName = DefaultCommandUtil::getModuleName();
            if(DefaultCommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->diconName = S2Base_StdinManager::getValue('dicon name ? : ');
            $this->validate($this->diconName);
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        } catch(Exception $e) {
            DefaultCommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        DefaultCommandUtil::validate($name,"Invalid dicon name. [ $name ]");
    }

    protected function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name     : {$this->moduleName} \n";
        print "  dicon file name : {$this->diconName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
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
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dicon/dicon.php');
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>