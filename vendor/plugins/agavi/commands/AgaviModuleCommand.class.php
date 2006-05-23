<?php
class AgaviModuleCommand implements S2Base_GenerateCommand 
{
    private $pathName   = S2BASE_PHP5_AG_DEFAULT_PATH;
    private $moduleName = S2BASE_PHP5_AG_DEFAULT_MODULE;
    private $actionName = S2BASE_PHP5_AG_DEFAULT_ACTION;
    private $viewName   = S2BASE_PHP5_AG_DEFAULT_VIEW;
    private $moduleDir;
    
    private $webappDir;
	private $agAppDir;
    
    public function getName ()
    {
        return "<agavi> module";
    }

    public function execute ()
    {
		$values = array();
        $values['pathName']   = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_PATH);
        $values['moduleName'] = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_MODULE);
        $values['actionName'] = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_ACTION);
        $values['viewName']   = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_VIEW);
		foreach ($values as $key => $value) {
			strlen($value) > 0 ? $this->$key = $value : null;
		}
        
        $this->webappDir = $this->pathName . S2BASE_PHP5_AG_WEBAPP_DIR;
        $this->moduleDir = $this->webappDir . '/modules/' . $this->moduleName;
        
        AgaviCommandUtil::validateProjectDir($this->webappDir);
        
        if (!$this->finalConfirm()){
            return;
        }
        
        print "[INFO ] generate agavi module : " . $this->moduleName . "\n";
        AgaviCommandUtil::execAgaviCmd('module',
                                       $this->pathName,
                                       $this->moduleName,
                                       $this->actionName,
                                       $this->viewName);
        
        AgaviCommandUtil::createLogicDirectories($this->moduleDir);
        AgaviCommandUtil::createTestDirectory($this->pathName,
                                              $this->moduleName);
        AgaviCommandUtil::appendSection2AutoloadIni($this->webappDir, $this->moduleName);
        AgaviCommandUtil::writeModuleIncFile4Test($this->pathName,
                                                  $this->moduleName);
        
        $this->prepareDiconFile();
    }
    
    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  project path        : {$this->pathName} \n";
        print "  module name         : {$this->moduleName} \n";
        print "  action name         : {$this->actionName} \n";
        print "  view name           : {$this->viewName} \n";

        $types = array('yes','no');
        $rep = S2Base_StdinManager::getValueFromArray($types,
                                        "confirmation");
        if ($rep == S2Base_StdinManager::EXIT_LABEL or 
            $rep == 'no'){
            return false;
        }
        return true;
    }
    
    private function prepareDiconFile ()
    {
        $incFile = $this->moduleDir . S2BASE_PHP5_DICON_DIR
                 . $this->actionName . S2BASE_PHP5_DICON_SUFFIX;
        AgaviCommandUtil::writeDiconFile($incFile,
                                         $this->moduleName,
                                         $this->actionName);
    }
}
?>