<?php
class sfDiconCommand implements S2Base_GenerateCommand
{
    private $pathName   = S2BASE_PHP5_SF_DEFAULT_PATH;
    private $appName;
    private $moduleName;
    private $actionName;
    private $actionDiconName;
    
    public function getName ()
    {
        return "dicon";
    }

    public function execute ()
    {
        $pathName = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_PATH);
        if (strlen($pathName) > 0) {
            $this->pathName = $pathName;
        }
        $appDir = $this->pathName . S2BASE_PHP5_DS . 'apps';
        try{
            $this->appName = sfCommandUtil::getAppName($appDir);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        if($this->appName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $moduleDir = $appDir . S2BASE_PHP5_DS .
                     $this->appName . S2BASE_PHP5_DS . 'modules';
        try{
            $this->moduleName = sfCommandUtil::getModuleName($moduleDir);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        
        $this->actionName = S2Base_StdinManager::getValue('action name ? : ');
        try{
            $this->validate($this->actionName);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        $this->actionDiconName = ucfirst($this->actionName) . 'Action';
        
        if (!$this->finalConfirm()){
            return;
        }
        
        $this->prepareDiconFile();
    }
    
    private function validate($name){
        S2Base_CommandUtil::validate($name, "Invalid action name. [ $name ]");
    }
    
    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  project path            : {$this->pathName} \n";
        print "  application name        : {$this->appName} \n";
        print "  action name             : {$this->actionName} \n";
        print "  action dicon file name  : {$this->actionDiconName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }
    
    public function prepareDiconFile ()
    {
        $srcFile = $this->pathName   . S2BASE_PHP5_DS .
                   "apps"            . S2BASE_PHP5_DS .
                   $this->appName    . S2BASE_PHP5_DS .
                   "modules"         . S2BASE_PHP5_DS .
                   $this->moduleName .
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->actionDiconName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SF_SKELETON_DIR .
                                                    'actions.dicon');
        $patterns = array("/@@APP_NAME@@/","/@@MODULE_NAME@@/");
        $replacements = array($this->appName,
                              $this->moduleName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>
