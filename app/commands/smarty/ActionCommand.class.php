<?php
class ActionCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $actionName;
    private $actionClassName;
    
    public function getName(){
        return "action";
    }

    public function execute(){
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        $this->actionName = S2Base_StdinManager::getValue('action name ? : ');
        $this->validate($this->actionName);
        $this->actionClassName = ucfirst($this->actionName) . "Action";
        $this->prepareFiles();
    }        

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid action name. [ $name ]");
    }
    
    private function prepareFiles(){
        $this->prepareActionFile();
        $this->prepareIncFile();
        $this->prepareHtmlFile();
        $this->prepareDiconFile();
    }
    
    private function prepareActionFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_ACTION_DIR . 
                   "{$this->actionClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'action.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->actionClassName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }

    private function prepareIncFile(){
        $incFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_ACTION_DIR .
                   "{$this->actionClassName}.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'action_inc.php');
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                    $this->moduleName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($incFile,$tempContent);       
        print "[INFO ] create : $incFile\n";
    }

    private function prepareHtmlFile(){
        $htmlFile = S2BASE_PHP5_MODULES_DIR . 
                     $this->moduleName . 
                     S2BASE_PHP5_VIEW_DIR . 
                     "{$this->actionName}" . 
                     S2Base_GenerateCommand::TPL_SUFFIX; 
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'action_html.php');
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                    $this->moduleName,
                                    $tempContent);   
        $tempContent = preg_replace("/@@ACTION_NAME@@/",
                                    $this->actionName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($htmlFile,$tempContent);
        print "[INFO ] create : $htmlFile\n";
    }

    private function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'action_dicon.php');
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                    $this->moduleName,
                                    $tempContent);   
        $tempContent = preg_replace("/@@COMPONENT_NAME@@/",
                                    $this->actionName,
                                    $tempContent);   
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                                    $this->actionClassName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }
}
?>