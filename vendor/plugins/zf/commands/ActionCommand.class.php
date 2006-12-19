<?php
class ActionCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $actionName;
    protected $dispatcher;
    protected $controllerClassName;
    protected $actionMethodName;
    protected $formatActionName;

    public function __construct(){
        require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfDispatcher.php';
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
            $this->moduleName = $this->getActionControllerName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->controllerClassName = $this->dispatcher->formatControllerName($this->moduleName);
            $this->actionName = S2Base_StdinManager::getValue('action name ? : ');
            $this->formatActionName = $this->dispatcher->formatName($this->actionName, false);
            $this->validate($this->formatActionName);
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
        S2Base_CommandUtil::validate($name,"Invalid action name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  controller name       : {$this->moduleName}" . PHP_EOL;
        print "  controller class name : {$this->controllerClassName}" . PHP_EOL;
        print "  action name           : {$this->actionName}" . PHP_EOL;
        print "  format action name    : {$this->formatActionName}" . PHP_EOL;
        print "  action method name    : {$this->actionMethodName}" . PHP_EOL;
        print "  action dicon file     : {$this->actionMethodName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  action template file  : {$this->actionName}" . S2BASE_PHP5_ZF_TPL_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareDiconFile();
    }
    
    protected function prepareActionFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->controllerClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempAction = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                    . '/skeleton/action/action.php');
        $patterns = array("/@@ACTION_NAME@@/",
                          "/@@TEMPLATE_NAME@@/");
        $replacements = array($this->actionMethodName,
                              $this->actionName . S2BASE_PHP5_ZF_TPL_SUFFIX);
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
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        $htmlFile = 'html.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/action/$htmlFile");
        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,
                              $this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->actionMethodName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/action/dicon.php');
        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_CLASS_NAME@@/");
        $replacements = array($this->moduleName,
                              $this->controllerClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
