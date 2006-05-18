<?php
require_once('AgaviCommandUtil.class.php');
class AgaviActionCommand implements S2Base_GenerateCommand
{
    private $pathName   = S2BASE_PHP5_AG_DEFAULT_PATH;
    private $moduleName = S2BASE_PHP5_AG_DEFAULT_MODULE;
    private $actionName = S2BASE_PHP5_AG_DEFAULT_ACTION;
    private $viewName   = S2BASE_PHP5_AG_DEFAULT_VIEW;
    private $moduleDir;
    
    public function getName ()
    {
        return "<agavi> action";
    }

    public function execute ()
    {
        $pathName = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_PATH);
        if (strlen($pathName) > 0) $this->pathName = $pathName;
        $targetDir = $this->pathName . S2BASE_PHP5_AG_MODULE_DIR;
        $this->moduleName = AgaviCommandUtil::getModuleName($targetDir);
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        
		$values = array();
        $values['actionName']  = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_ACTION);
        $values['viewName']    = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_VIEW);
		foreach( $values as $key => $value )
		{
			strlen($value) > 0 ? $this->$key = $value : null;
		}
        
        print "[INFO ] generate agavi action : " . $this->actionName . "\n";
        AgaviCommandUtil::execAgaviCmd('action',
                                       $this->pathName,
                                       $this->moduleName,
                                       $this->actionName,
                                       $this->viewName);
        
        $this->moduleDir = $targetDir . S2BASE_PHP5_DS . $this->moduleName;
        $this->prepareDiconFile();
    }
    
    private function prepareDiconFile ()
    {
        $incFile = $this->moduleDir . S2BASE_PHP5_DICON_DIR .
                   $this->actionName . S2BASE_PHP5_DICON_SUFFIX;
        AgaviCommandUtil::writeDiconFile($incFile,
                                         $this->moduleName,
                                         $this->actionName);
        print "[INFO ] create : $incFile\n";
    }
}
?>