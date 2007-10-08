<?php
require_once('ModuleCommand.class.php');
class AnaModuleCommand extends ModuleCommand {

    public function getName(){
        return "ana module";
    }

    public function isAvailable(){
        return true;
    }

    public function prepareActionControllerClassFile(){
        $srcFile = $this->appModuleDir
                 . S2BASE_PHP5_DS . 'controllers'
                 . S2BASE_PHP5_DS . $this->controllerClassFile . S2BASE_PHP5_CLASS_SUFFIX; 

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeletons/ana-module/controller.tpl");
        $keys = array("/@@CONTROLLER_CLASS_NAME@@/");
        $reps = array($this->controllerClassName);
        $tempContent = preg_replace($keys, $reps, $tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public function prepareHtmlFile(){
        $this->prepareIndexFile();
        $this->prepareLoginFile();
        $this->prepareValidateIniFile();
    }

    public function prepareIndexFile(){
        if (self::isStandardView()) {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . 'scripts'
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . 'index.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        } else {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . 'index.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        }
        $viewSuffix = self::getViewSuffixName();
        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeletons/module/html_header$viewSuffix.tpl");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeletons/ana-module/html$viewSuffix.tpl");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeletons/module/html_footer.tpl");
        }

        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/");
        $replacements = array($this->moduleName,
                              $this->controllerName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public function prepareLoginFile(){
        if (self::isStandardView()) {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . 'scripts'
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . 'login.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        } else {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . 'login.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        }
        $viewSuffix = self::getViewSuffixName();
        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeletons/module/html_header$viewSuffix.tpl");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeletons/ana-module/login$viewSuffix.tpl");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeletons/module/html_footer.tpl");
        }

        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/");
        $replacements = array($this->moduleName,
                              $this->controllerName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareValidateIniFile(){
        $srcFile = $this->appModelDir
                 . S2BASE_PHP5_DS . $this->controllerName
                 . S2BASE_PHP5_DS . ModuleCommand::VALIDATE_DIR
                 . S2BASE_PHP5_DS . 'ana.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/action/validate.ini.tpl');
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . '/skeletons/ana-module/validate.ini.tpl');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
