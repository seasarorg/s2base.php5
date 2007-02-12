<?php
class PageCommand implements S2Base_GenerateCommand {
    protected $moduleName;
    protected $pageName;
    protected $pageClassName;
    
    public function getName(){
        return "page";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->pageName = S2Base_StdinManager::getValue('page name ? : ');
            $this->validate($this->pageName);
            $this->pageClassName = ucfirst($this->pageName);
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
        S2Base_CommandUtil::validate($name,"Invalid page name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name            : {$this->moduleName}" . PHP_EOL;
        print "  page name            : {$this->pageName}" . PHP_EOL;
        print "  page class name      : {$this->pageClassName}" . PHP_EOL;
        print "  page dicon file name : {$this->pageClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  page template file   : {$this->pageName}" . S2BASE_PHP5_PRADO_PAGE_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->preparePageFile();
        $this->prepareTemplateFile();
        $this->prepareDiconFile();
    }

    protected function preparePageFile(){
        $srcFile = DOCUMENT_ROOT_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_PRADO_PAGES_DIR
                 . $this->pageClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page/page.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->pageClassName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareTemplateFile(){
        $srcFile = DOCUMENT_ROOT_DIR 
                 . $this->moduleName 
                 . S2BASE_PHP5_PRADO_PAGES_DIR 
                 . $this->pageName 
                 . S2BASE_PHP5_PRADO_PAGE_SUFFIX; 
        $templateFile = 'template.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . "/skeleton/page/$templateFile");
        $patterns = array("/@@MODULE_NAME@@/","/@@PAGE_NAME@@/");
        $replacements = array($this->moduleName,$this->pageName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDiconFile(){
        $srcFile = DOCUMENT_ROOT_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_PRADO_DICON_DIR
                 . $this->pageClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page/dicon.php');
        $patterns = array("/@@MODULE_NAME@@/","/@@COMPONENT_NAME@@/","/@@CLASS_NAME@@/");
        $replacements = array($this->moduleName,$this->pageName,$this->pageClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
