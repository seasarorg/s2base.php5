<?php
class AgaviProjectCommand implements S2Base_GenerateCommand
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
        return "<agavi> project";
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

        if (!$this->finalConfirm()){
            return;
        }
        
        print "[INFO ] generate agavi project (heavy) : " . $this->pathName . "\n";
        AgaviCommandUtil::execAgaviCmd('project',
                                       $this->pathName,
                                       $this->moduleName,
                                       $this->actionName,
                                       $this->viewName);
        
        $this->webappDir = $this->pathName . S2BASE_PHP5_AG_WEBAPP_DIR;
        $this->moduleDir = $this->webappDir . '/modules/' . $this->moduleName;
        
        $this->createDirectory();
        $this->prepareS2AgaviFiles();
        $this->writeProjectPathFile();
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
    
    private function validate ($name){
        S2Base_CommandUtil::validate($name,"Invalid Value. [ $name ]");
    }
    
    private function createDirectory ()
    {
        AgaviCommandUtil::createDirectory($this->webappDir . '/lib/s2agavi');
        AgaviCommandUtil::createLogicDirectories($this->moduleDir);
        AgaviCommandUtil::createTestDirectory($this->pathName,
                                              $this->moduleName);
    }
    
    private function prepareS2AgaviFiles ()
    {
        $this->prepareIndexPhpFile ();
		$this->prepareConfigPhpFile();
        $this->prepareDiconFile();
        $btNames = array('TraversalAutoloadConfigHandler.class.php',
                         's2agavi.php',
                         'S2Base_AgaviController.class.php');
        $this->prepareS2AgaviCoreFiles($btNames);
        $iniNames = array('autoload.ini',
						  'config_handlers.ini',
						  'contexts.ini');
        $this->prepareIniFiles($iniNames);
        AgaviCommandUtil::appendSection2AutoloadIni($this->webappDir, $this->moduleName);
        AgaviCommandUtil::writeModuleIncFile4Test($this->pathName,
                                                  $this->moduleName);
    }
    
    private function prepareIndexPhpFile ()
    {
        $incFile = $this->pathName . '/www/index.php';
        $tempContent = S2Base_CommandUtil::readFile($incFile);
        @unlink($incFile);
		
		$tempContent = explode("\n", $tempContent);
		$s2agaviPath = $this->webappDir . S2BASE_PHP5_AG_S2AGAVI_DIR . 's2agavi.php';
		$replaceLine = "require_once('".$s2agaviPath."');";
		$data = array();
		$pattern = "/(\'\/.+agavi\.php\')/";
		foreach( $tempContent as $key => $value ) {
			if (preg_match($pattern, $value, $matches)) {
				$this->agAppDir = $matches[1];
				$value = null;
				$data[] = $replaceLine;
			} else {
				$data[] = $value;
			}
		}

		$tempContent = implode("\n", $data);
        CmdCommand::writeFile($incFile,$tempContent);
    }
    
	private function prepareConfigPhpFile ()
	{
		$incFile = $this->webappDir . S2BASE_PHP5_DS . 'config.php';
		$tempContent = S2Base_CommandUtil::readFile($incFile);
		@unlink($incFile);
		
        $incSkel = S2Base_CommandUtil::readFile(S2BASE_PHP5_AG_SKELETON_DIR .
                                                's2base.inc.php');
        $incSkel = preg_replace("/@@S2BASE_PHP5_ROOT@@/",
                                S2BASE_PHP5_ROOT,
                                $incSkel);
		$tempContent = preg_replace("/\n\n\?>/",
									$incSkel,
									$tempContent);
		CmdCommand::writeFile($incFile, $tempContent);
	}

    private function prepareDiconFile ()
    {
        $incFile = $this->moduleDir .
                   S2BASE_PHP5_DICON_DIR .
                   $this->actionName .
                   S2BASE_PHP5_DICON_SUFFIX;
        AgaviCommandUtil::writeDiconFile($incFile,
                                         $this->moduleName,
                                         $this->actionName);
    }
    
    private function prepareS2AgaviCoreFiles ($btNames)
    {
        foreach ($btNames as $btName) {
            $incFile = $this->webappDir . S2BASE_PHP5_AG_S2AGAVI_DIR . $btName;
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_AG_SKELETON_CORE_DIR .
                                                        $btName);
			$tempContent = preg_replace("/@@AG_APP_DIR@@/",
										$this->agAppDir,
										$tempContent);
            CmdCommand::writeFile($incFile,$tempContent);
        }
    }
    
    private function prepareIniFiles ($iniNames)
    {
        foreach ($iniNames as $iniName) {
            $incFile = $this->webappDir . S2BASE_PHP5_AG_CONFIG_DIR . $iniName;
            @unlink($incFile);
            
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_AG_SKELETON_CORE_DIR .
                                                        $iniName);
            /*
            $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                        $this->moduleName,
                                        $tempContent);  
            */
            CmdCommand::writeFile($incFile,$tempContent);
        }
    }
    
    private function writeProjectPathFile ()
    {
        @unlink(S2BASE_PHP5_AG_PATH_CACHE);
        $content = 'projectPath = '.$this->pathName;
        if(!file_put_contents(S2BASE_PHP5_AG_PATH_CACHE,$content,LOCK_EX)){
            throw new Exception("Cannot write to file [ $filename ]");
        }
    }
}
?>
