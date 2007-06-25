<?php
class sfInitProject
{
    private $pathName   = S2BASE_PHP5_SF_DEFAULT_PATH;
    private $projectName;
    private $appName;
    private $moduleName;
    
    public function execute ()
    {
        $pathName = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_PATH);
        if (strlen($pathName) > 0) {
            $this->pathName = $pathName;
        }
        
        $this->projectName = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_PROJECT);
        $this->appName     = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_APP);
        $this->validate($this->appName);
        $this->moduleName  = sfCommandUtil::getValueFromType(S2BASE_PHP5_SF_MODULE);
        $this->validate($this->moduleName);
        
        if (!$this->finalConfirm()){
            return;
        }
        
        sfCommandUtil::execSfCmd('init-project', $this->projectName, $this->pathName);
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
        print "  project name       : {$this->projectName} \n";
        print "  application name   : {$this->appName} \n";
        print "  module name        : {$this->moduleName} \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }
    
    private function prepareFiles ()
    {
        $this->prepareConfigPhpFile();
        // $this->prepareAutoloadYmlFile();
        $this->writeProjectPathFile();
        
        sfCommandUtil::$attributes['pathName']   = $this->pathName;
        sfCommandUtil::$attributes['appName']    = $this->appName;
        sfCommandUtil::$attributes['moduleName'] = $this->moduleName;
        sfCommandUtil::copyMyFrontWebController();
        sfCommandUtil::copyFactoriesYml();
        sfCommandUtil::createLogicDirectories();
        sfCommandUtil::createTestDirectories();
        sfCommandUtil::prepareTestIncFile();
        sfCommandUtil::prepareModuleAutoloadYmlFile();
        sfCommandUtil::prepareModuleDiconFile();
    }
    
    private function prepareAutoloadYmlFile ()
    {
        $srcFile = $this->pathName . S2BASE_PHP5_DS .
                   "config". S2BASE_PHP5_DS .
                   "autoload.yml";
        @unlink($srcFile);
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SF_SKELETON_DIR .
                                                    'autoload.yml');
        $tempContent = preg_replace("/@@S2BASE_PHP5_ROOT@@/",
                                    S2BASE_PHP5_ROOT,
                                    $tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }
    
    private function prepareConfigPhpFile ()
    {
        $srcFile = $this->pathName . S2BASE_PHP5_DS .
                   "config". S2BASE_PHP5_DS .
                   "config.php";
        // @unlink($srcFile);
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SF_SKELETON_DIR .
                                                    'config.php');
        $tempContent = preg_replace("/@@S2BASE_PHP5_ROOT@@/",
                                    S2BASE_PHP5_ROOT,
                                    $tempContent);
        file_put_contents($srcFile, $tempContent, FILE_APPEND);  
        // CmdCommand::writeFile($srcFile,$tempContent);
    }
    
    private function writeProjectPathFile ()
    {
        @unlink(S2BASE_PHP5_SF_PATH_CACHE);
        $content = 'projectPath = '.$this->pathName;
        if(!file_put_contents(S2BASE_PHP5_SF_PATH_CACHE,$content,LOCK_EX)){
            throw new Exception("Cannot write to file [ $filename ]");
        }
    }
}
?>
