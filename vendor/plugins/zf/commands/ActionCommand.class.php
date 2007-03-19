<?php
class ActionCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $controllerName;
    protected $actionName;
    protected $dispatcher;
    protected $controllerClassName;
    protected $controllerClassFile;
    protected $actionMethodName;
    protected $srcModuleDir;
    protected $srcCtlDir;
    protected $testModuleDir;
    protected $testCtlDir;

    public function __construct(){
        $this->dispatcher = new S2Base_ZfDispatcher();
    }

    public function getName(){
        return "action";
    }

    protected function getActionControllerName(){
        $modules = S2Base_CommandUtil::getAllModules();
        if(count($modules) == 0){
            throw new Exception("Module not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,'Controller list');
    }

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
            list($this->controllerName, $this->controllerClassName, $this->controllerClassFile) = 
                ModuleCommand::getControllerNames($this->dispatcher, $this->moduleName, $this->controllerName);

            $this->actionName = S2Base_StdinManager::getValue('action name ? : ');
            $this->actionMethodName = $this->dispatcher->formatActionName($this->actionName);
            $this->validate($this->actionMethodName);
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
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name           : {$this->moduleName}" . PHP_EOL;
        print "  controller name       : {$this->controllerName}" . PHP_EOL;
        print "  controller class name : {$this->controllerClassName}" . PHP_EOL;
        print "  action name           : {$this->actionName}" . PHP_EOL;
        print "  action method name    : {$this->actionMethodName}" . PHP_EOL;
        print "  action dicon file     : {$this->actionMethodName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  action template file  : {$this->actionName}" . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->srcModuleDir  = S2BASE_PHP5_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->srcCtlDir     = $this->srcModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;
        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareDiconFile();
        $this->prepareValidateIniFile();
    }
    
    protected function prepareActionFile(){
        $srcFile = $this->srcModuleDir
                 . $this->controllerClassFile
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempAction = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                    . '/skeleton/action/action.tpl');
        $patterns = array("/@@ACTION_NAME@@/",
                          "/@@TEMPLATE_NAME@@/");
        $replacements = array($this->actionMethodName,
                              $this->actionName . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX);
        $tempAction = preg_replace($patterns,$replacements,$tempAction);

        $tempContent = S2Base_CommandUtil::readFile($srcFile);

        $reg = '/\s\s\s\s\/\*\*\sS2BASE_PHP5\sACTION\sMETHOD\s\*\*\//';
        if (!preg_match($reg, $tempContent)) {
            print PHP_EOL;
            print "[INFO ] please copy & paste to $srcFile" . PHP_EOL;
            print $tempAction . PHP_EOL;
            print PHP_EOL;
            return;
        }

        $tempContent = preg_replace($reg, $tempAction, $tempContent, 1);
        if(!file_put_contents($srcFile,$tempContent,LOCK_EX)){
            S2Base_CommandUtil::showException(new Exception("Cannot write to file [ $srcFile ]"));
        } else {
            print "[INFO ] modify : $srcFile" . PHP_EOL;
        }
    }

    protected function prepareHtmlFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 

        $viewSuffix = ModuleCommand::getViewSuffixName();
        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/module/html_header$viewSuffix.tpl");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeleton/action/html$viewSuffix.tpl");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/module/html_footer.tpl");
        }

        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,
                              $this->controllerName,
                              $this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDiconFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DICON_DIR
                 . $this->actionMethodName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/action/dicon.tpl');
        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_CLASS_NAME@@/");
        $replacements = array($this->controllerName,
                              $this->controllerClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareValidateIniFile(){
        $srcFile = $this->srcCtlDir
                 . ModuleCommand::VALIDATE_DIR
                 . S2BASE_PHP5_DS
                 . $this->actionName
                 . '.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/action/validate.ini.tpl');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
