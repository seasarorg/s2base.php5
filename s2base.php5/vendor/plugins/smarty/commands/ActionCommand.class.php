<?php
class ActionCommand implements S2Base_GenerateCommand {
    const ACTION_CLASS_SUFFIX = 'Action';
    protected $moduleName;
    protected $actionName;
    protected $actionClassName;
    
    public function getName(){
        return "action";
    }

    public function execute(){
        try{
            $this->moduleName = DefaultCommandUtil::getModuleName();
            if(DefaultCommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->actionName = S2Base_StdinManager::getValue('action name ? : ');
            $this->validate($this->actionName);
            $this->actionClassName = ucfirst($this->actionName) . self::ACTION_CLASS_SUFFIX;
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        } catch(Exception $e) {
            DefaultCommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        DefaultCommandUtil::validate($name,"Invalid action name. [ $name ]");
    }

    protected function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name            : {$this->moduleName} \n";
        print "  action name            : {$this->actionName} \n";
        print "  action class name      : {$this->actionClassName} \n";
        print "  action dicon file name : {$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        print "  action template file   : {$this->actionName}" . S2BASE_PHP5_SMARTY_TPL_SUFFIX . "\n";
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareDiconFile();
    }

    protected function prepareActionFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_ACTION_DIR
                 . $this->actionClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/action/action.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->actionClassName,
                             $tempContent);   
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareHtmlFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                     $this->moduleName . 
                     S2BASE_PHP5_VIEW_DIR . 
                     "{$this->actionName}" . 
                     S2BASE_PHP5_SMARTY_TPL_SUFFIX; 
        $htmlFile = defined('S2BASE_PHP5_LAYOUT') ? 'html_layout.php' : 'html.php';
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/action/$htmlFile");
        $patterns = array("/@@MODULE_NAME@@/","/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/action/dicon.php');
        $patterns = array("/@@MODULE_NAME@@/","/@@COMPONENT_NAME@@/","/@@CLASS_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName,$this->actionClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
