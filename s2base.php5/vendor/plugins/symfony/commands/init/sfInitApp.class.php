<?php
class sfInitApp
{
    private $pathName   = S2BASE_PHP5_SF_DEFAULT_PATH;
    private $appName;
    private $moduleName;
    
    public function execute ()
    {
        $pathName = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_PATH);
        if (strlen($pathName) > 0) {
            $this->pathName = $pathName;
        }
        
        $this->appName     = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_APP);
        $this->validate($this->appName);
        $this->moduleName  = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_MODULE);
        $this->validate($this->moduleName);
        
        if (!$this->finalConfirm()){
            return;
        }
        
        sfCommandUtil::execSfCmd('init-app', $this->appName, $this->pathName);
        sfCommandUtil::execSfCmd('init-module',
                                 $this->appName . ' ' . $this->moduleName,
                                 $this->pathName);
        $this->prepareFiles();
    }
    
    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  project path       : {$this->pathName} \n";
        print "  application name   : {$this->appName} \n";
        print "  module name        : {$this->moduleName} \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }
    
    private function prepareFiles ()
    {
        sfCommandUtil::$attributes['pathName']   = $this->pathName;
        sfCommandUtil::$attributes['appName']    = $this->appName;
        sfCommandUtil::$attributes['moduleName'] = $this->moduleName;
        sfCommandUtil::copyMyFrontWebController();
        sfCommandUtil::copyFactoriesYml();
        sfCommandUtil::createLogicDirectories();
        sfCommandUtil::prepareModuleAutoloadYmlFile();
        sfCommandUtil::prepareModuleServiceIfaceFile();
    }
}
?>
