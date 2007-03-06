<?php
/**
 * Diconファイルを生成します。
 * 
 * 生成ファイル
 * <ul>
 *   <li>app/modules/module名/コントローラ名/dicon/dicon名.dicon</li>
 * </ul>
 * 
 */
class DiconCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $controllerName;
    protected $diconName;

    protected $srcModuleDir;
    protected $srcCtlDir;
    protected $testModuleDir;
    protected $testCtlDir;

    /**
     * @see S2Base_GenerateCommand::getName()
     */    
    public function getName(){
        return "dicon";
    }

    /**
     * @see S2Base_GenerateCommand::execute()
     */
    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->controllerName = ModuleCommand::getActionControllerName($this->moduleName);
            if(S2Base_CommandUtil::isListExitLabel($this->controllerName)){
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
        print "  controller name : {$this->controllerName}" . PHP_EOL;
        print "  dicon file name : {$this->diconName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->srcModuleDir  = S2BASE_PHP5_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->srcCtlDir     = $this->srcModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;

        $this->prepareDiconFile();
    }
    
    protected function prepareDiconFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DICON_DIR
                 . $this->diconName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dicon/dicon.tpl');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>