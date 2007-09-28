<?php
class UiCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $uiType;
    protected $uiFile;

    /**
     * @see S2Base_GenerateCommand::getName()
     */    
    public function getName(){
        return 'ui';
    }

    /**
     * @see S2Base_GenerateCommand::isAvailable()
     */
    public function isAvailable(){
        return true;
    }

    /**
     * @see S2Base_GenerateCommand::execute()
     */
    public function execute(){
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
            return;
        }

        $this->uiType = S2Base_StdinManager::getValueFromArray(array('cli','web'), 'ui type');
        if (S2Base_CommandUtil::isListExitLabel($this->uiType)){
            return;
        }

        $this->uiFile = $this->moduleName . '.php';
        $uiFileTmp = S2Base_StdinManager::getValue("file name ? [{$this->uiFile}] : ");
        if (trim($uiFileTmp) !== '') {
            $this->uiFile = $uiFileTmp;
        }

        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name : {$this->moduleName}" . PHP_EOL;
        print "  ui     type : {$this->uiType}" . PHP_EOL;
        print "  file   name : {$this->uiFile}" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareUiFile();
    }

    protected function prepareUiFile(){

        if ($this->uiType === 'cli') {
            $uiDir = S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'bin';
        } else {
            $uiDir = S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'public';
        }
        S2Base_CommandUtil::createDirectory($uiDir);

        $srcFile = $uiDir . S2BASE_PHP5_DS . $this->uiFile;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETONS_DIR
                     . "/ui/{$this->uiType}.tpl");
        $patterns = array("/@@MODULE_NAME@@/");
        $replacements = array($this->moduleName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
