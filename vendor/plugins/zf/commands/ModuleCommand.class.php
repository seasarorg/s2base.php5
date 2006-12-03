<?php
class ModuleCommand implements S2Base_GenerateCommand {
    protected $moduleName;
    protected $srcDirectory;
    protected $testDirectory;
    protected $controllerClassName;
    protected $dispatcher;

    public function __construct(){
        require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfDispatcher.php';
        $this->dispatcher = new S2Base_ZfDispatcher();
    }

    public function getName(){
        return "module & action controller";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_StdinManager::getValue('controller name ? : ');
            $this->validate($this->moduleName);
            $this->controllerClassName = $this->dispatcher->formatControllerName($this->moduleName);
            $this->srcDirectory = S2BASE_PHP5_MODULES_DIR . $this->moduleName;
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
        print "  controller name       : {$this->moduleName}" . PHP_EOL;
        print "  controller class name : {$this->controllerClassName}" . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function createDirectory(){
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_DICON_DIR,
            S2BASE_PHP5_ENTITY_DIR,
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_SERVICE_DIR,
            S2BASE_PHP5_VIEW_DIR);
        S2Base_CommandUtil::createDirectory($this->srcDirectory);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->srcDirectory. $dir);
        }

        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_SERVICE_DIR);
        S2Base_CommandUtil::createDirectory($this->testDirectory);
        foreach($dirs as $dir){
            S2Base_CommandUtil::createDirectory($this->testDirectory. $dir);
        }
    }

    protected function prepareFiles(){
        $this->prepareActionControllerClassFile();
        $this->prepareModuleServiceInterfaceFile();
        $this->prepareModuleIncFile();
        $this->prepareIndexFile();
    }

    protected function prepareActionControllerClassFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->controllerClassName
                 . S2BASE_PHP5_CLASS_SUFFIX; 

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/module/controller.php");
        $keys = array("/@@CONTROLLER_CLASS_NAME@@/",
                      "/@@SERVICE_CLASS_NAME@@/",
                      "/@@CONTROLLER_NAME@@/",
                      "/@@TEMPLATE_NAME@@/");
        $reps = array($this->controllerClassName,
                      $this->moduleName . 'Service',
                      $this->moduleName,
                      'index' . S2BASE_PHP5_ZF_TPL_SUFFIX);
        $tempContent = preg_replace($keys, $reps, $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareModuleServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . '/service/'
                 . ucfirst($this->moduleName) . 'Service'
                 . S2BASE_PHP5_CLASS_SUFFIX; 

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/module/service.php");
        $keys = array("/@@SERVICE_CLASS_NAME@@/");
        $reps = array(ucfirst($this->moduleName) . 'Service');
        $tempContent = preg_replace($keys, $reps, $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareModuleIncFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName . S2BASE_PHP5_DS
                 . "{$this->moduleName}.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/module/include.php');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareIndexFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_VIEW_DIR
                 . 'index'
                 . S2BASE_PHP5_ZF_TPL_SUFFIX; 

        $htmlFile = 'index.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/module/$htmlFile");
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                    $this->moduleName,
                                    $tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
