<?php
class ActionCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $actionName;
    private $actionClassName;
    
    public function getName(){
        return "action";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }

        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        $this->actionName = S2Base_StdinManager::getValue('action name ? : ');
        try{
            $this->validate($this->actionName);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        $this->actionClassName = ucfirst($this->actionName) . "Action";
        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }        

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid action name. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name            : {$this->moduleName} \n";
        print "  action name            : {$this->actionName} \n";
        print "  action class name      : {$this->actionClassName} \n";
        print "  action dicon file name : {$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        print "  action template file   : {$this->actionName}" . S2BASE_PHP5_SMARTY_TPL_SUFFIX . "\n";
        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function prepareFiles(){
        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareDiconFile();
    }
    
    private function prepareActionFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_ACTION_DIR
                 . $this->actionClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/action/action.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->actionClassName,
                             $tempContent);   
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareHtmlFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                     $this->moduleName . 
                     S2BASE_PHP5_VIEW_DIR . 
                     "{$this->actionName}" . 
                     S2BASE_PHP5_SMARTY_TPL_SUFFIX; 
        $htmlFile = defined('S2BASE_PHP5_LAYOUT') ? 'html_layout.php' : 'html.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/action/$htmlFile");
        $patterns = array("/@@MODULE_NAME@@/","/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/action/dicon.php');
        $patterns = array("/@@MODULE_NAME@@/","/@@COMPONENT_NAME@@/","/@@CLASS_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName,$this->actionClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>