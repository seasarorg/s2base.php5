<?php
class AjaxTplCommand implements S2Base_GenerateCommand {
    const JS_DIR = '/public/js/';
    const JS_SUFFIX = '.js';

    protected $moduleName;
    protected $tplName;

    public function getName(){
        return "ajax tpl";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->tplName = S2Base_StdinManager::getValue('tpl name ? : ');
            $this->validate($this->tplName);
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
        S2Base_CommandUtil::validate($name,"Invalid tpl name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name   : {$this->moduleName}" . PHP_EOL;
        print "  template file : {$this->tplName}" . S2BASE_PHP5_SMARTY_TPL_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        S2Base_CommandUtil::createDirectory(S2BASE_PHP5_ROOT . self::JS_DIR);
        S2Base_CommandUtil::createDirectory(S2BASE_PHP5_ROOT . self::JS_DIR . $this->moduleName);
        $this->prepareHtmlFile();
        $this->prepareS2BaseJSFile();
        $this->prepareTplJSFile();
    }

    protected function prepareS2BaseJSFile(){
        $srcFile = S2BASE_PHP5_ROOT
                 . self::JS_DIR 
                 . 's2base' 
                 . self::JS_SUFFIX; 
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/ajax-tpl/s2base-js.php");
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareTplJSFile(){
        $srcFile = S2BASE_PHP5_ROOT
                 . self::JS_DIR 
                 . $this->moduleName 
                 . S2BASE_PHP5_DS 
                 . $this->tplName 
                 . self::JS_SUFFIX; 
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/ajax-tpl/tpl-js.php");
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareHtmlFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR 
                 . $this->moduleName 
                 . S2BASE_PHP5_VIEW_DIR 
                 . $this->tplName 
                 . S2BASE_PHP5_SMARTY_TPL_SUFFIX; 
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/ajax-tpl/html.php");
        $patterns = array("/@@MODULE_NAME@@/","/@@TPL_NAME@@/");
        $replacements = array($this->moduleName,$this->tplName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
