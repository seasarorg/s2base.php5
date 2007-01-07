<?php
class ModuleCommand implements S2Base_GenerateCommand {
    protected $moduleName;
    protected $srcDirectory;
    protected $pubDirectory;
    protected $testDirectory;

    public function getName(){
        return "module";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_StdinManager::getValue('module name ? : ');
            $this->validate($this->moduleName);
            $this->srcDirectory = S2BASE_PHP5_MODULES_DIR . $this->moduleName;
            $this->pubDirectory = DOCUMENT_ROOT_DIR . $this->moduleName;
            $this->testDirectory = S2BASE_PHP5_TEST_MODULES_DIR . $this->moduleName;
            if (!$this->finalConfirm()){
                return;
            }
            $this->createDirectory();
            $this->prepareFiles();
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid module name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name : {$this->moduleName}" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function createDirectory(){
		// Src Dir
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_DICON_DIR,
            S2BASE_PHP5_ENTITY_DIR,
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_PRADO_LOGIC_DIR,
            S2BASE_PHP5_VIEW_DIR);
        S2Base_CommandUtil::createDirectory($this->srcDirectory);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->srcDirectory. $dir);
        }

		// Test Dir
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_LOGIC_DIR);
        S2Base_CommandUtil::createDirectory($this->testDirectory);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->testDirectory. $dir);
        }

		// Public Dir
        $dirs = array(
            S2BASE_PHP5_PRADO_ASSETS_DIR,
            S2BASE_PHP5_PRADO_PROTECTED_DIR,
            S2BASE_PHP5_PRADO_PAGES_DIR,
            S2BASE_PHP5_PRADO_DICON_DIR,
            S2BASE_PHP5_PRADO_RUNTIME_DIR);
        S2Base_CommandUtil::createDirectory($this->pubDirectory);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->pubDirectory. $dir);
        }
		// Chmod
		chmod($this->pubDirectory. S2BASE_PHP5_PRADO_ASSETS_DIR, 0777);
		chmod($this->pubDirectory. S2BASE_PHP5_PRADO_RUNTIME_DIR, 0777);
    }

    protected function prepareFiles(){
        $this->prepareModuleIncFile();
        $this->prepareIndexFile();
        $this->prepareApplicationConfigFile();
    }

    protected function prepareModuleIncFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . S2BASE_PHP5_DS .
                   "{$this->moduleName}.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/module/include.php');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareIndexFile(){
        $srcFile = DOCUMENT_ROOT_DIR . 
                     $this->moduleName . S2BASE_PHP5_DS .
                     "index.php"; 

        $indexFile = 'index.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . "/skeleton/module/$indexFile");
		$tempContent = preg_replace("/@@S2BASE_ENVIRONMENT_INC_PATH@@/",
                       				S2BASE_PHP5_ROOT . '/config/environment.inc.php',
                         			$tempContent);   

        $tempContent = preg_replace("/@@PRADO_RUNTIME_ENVIRONMENT_INC_PATH@@/",
                                    S2BASE_PHP5_ROOT . '/vendor/plugins/prado/config/runtime_environment.inc.php',
                                    $tempContent);   
		$tempContent = preg_replace("/@@S2BASE_MODULE_ENVIRONMENT_INC_PATH@@/",
		                            S2BASE_PHP5_MODULES_DIR . 
									$this->moduleName . S2BASE_PHP5_DS .
				                   "{$this->moduleName}.inc.php",
							        $tempContent);   

        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareApplicationConfigFile(){
        $srcFile = DOCUMENT_ROOT_DIR . 
       				$this->moduleName .
					S2BASE_PHP5_PRADO_PROTECTED_DIR .
                    "application.xml"; 

        $appConfigFile = 'application.xml.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . "/skeleton/module/$appConfigFile");
		$tempContent = preg_replace("/@@MODULE_NAME@@/",
                       				$this->moduleName,
                         			$tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
