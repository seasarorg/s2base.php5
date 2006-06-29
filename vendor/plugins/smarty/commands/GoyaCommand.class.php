<?php
class GoyaCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $actionName;
    private $actionClassName;
    private $serviceClassName;
    private $serviceInterfaceName;
    private $daoInterfaceName;
    private $entityClassName;
    private $tableName;
    private $cols;
    
    public function getName(){
        return "goya";
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

        $name = S2Base_StdinManager::getValue('action name ? : ');
        try{
            $this->validate($name);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }

        $rep = S2Base_StdinManager::isYes('use dao ?');
        if(!$rep){
            $this->setNamesWithoutDao($name);
            if (!$this->finalConfirmWithoutDao()){
                return;
            }
            $this->prepareFilesWithoutDao();   
            return;         
        }

        $rep = S2Base_StdinManager::isYes('use commons dao ?');

        if($rep){
            try{
                $daoName = $this->getDaoFromCommonsDao();
            } catch(Exception $e) {
                CmdCommand::showException($e);
                return;
            }
                
            if ($daoName == S2Base_StdinManager::EXIT_LABEL){
                return;
            }
            $this->setNamesWithCommonsDao($name,$daoName);
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFilesWithCommonsDao();
        }else{
            $this->setNames($name);
            $this->tableName = S2Base_StdinManager::getValue("table name ? [{$name}] : ");
            if(trim($this->tableName) == ''){
                $this->tableName = $name;
            } 
            $this->validate($this->tableName);

            $cols = S2Base_StdinManager::getValue("columns ? (id,name,--,) : ");
            $this->cols = explode(',',$cols);
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        }
    }

    public static function getDaoFromCommonsDao(){
        $commonsDaoDir = S2BASE_PHP5_ROOT . '/app/commons/dao';
        $entries = scandir($commonsDaoDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $commonsDaoDir ]");
        }
        $daos = array();
        foreach($entries as $entry){
            $maches = array();
            if(preg_match("/(\w+Dao)\.class\.php$/",$entry,$maches)){
                $daos[] = $maches[1];
            }
        }
        if(count($daos) == 0){
            throw new Exception("dao not found at all in [ $commonsDaoDir ]");
        }

        $daoName = S2Base_StdinManager::getValueFromArray($daos,
                                        "dao list");
        return $daoName;
    }

    private function setNames($name){
        $this->actionName = $name;
        $name = ucfirst($name);
        $this->actionClassName = $name . "Action";
        $this->serviceInterfaceName = $name . "Service";
        $this->serviceClassName = $name . "ServiceImpl";
        $this->daoInterfaceName = $name . "Dao";
        $this->entityClassName = $name . "Entity";
    }

    private function setNamesWithoutDao($name){
        $this->actionName = $name;
        $name = ucfirst($name);
        $this->actionClassName = $name . "Action";
        $this->serviceInterfaceName = $name . "Service";
        $this->serviceClassName = $name . "ServiceImpl";
    }

    private function setNamesWithCommonsDao($name,$daoName){
        $this->actionName = $name;
        $name = ucfirst($name);
        $this->actionClassName = $name . "Action";
        $this->serviceInterfaceName = $name . "Service";
        $this->serviceClassName = $name . "ServiceImpl";
        $this->daoInterfaceName = $daoName;
        $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
        $this->tableName = 'auto defined';
        $this->cols = array('auto defined');
    }
    
    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name             : {$this->moduleName} \n";
        print "  action name             : {$this->actionName} \n";
        print "  action class name       : {$this->actionClassName} \n";
        print "  action dicon file name  : {$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        print "  action tpl file name    : {$this->actionName}.tpl \n";
        print "  service interface name  : {$this->serviceInterfaceName} \n";
        print "  service class name      : {$this->serviceClassName} \n";
        print "  service test class name : {$this->serviceClassName}Test \n";
        print "  service dicon file name : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        print "  dao interface name      : {$this->daoInterfaceName} \n";
        print "  dao test class name     : {$this->daoInterfaceName}Test \n";
        print "  entity class name       : {$this->entityClassName} \n";
        print "  table name              : {$this->tableName} \n";
        $cols = implode(', ',$this->cols);
        print "  columns                 : $cols \n";

        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function finalConfirmWithoutDao(){
        print "\n[ generate information ] \n";
        print "  module name             : {$this->moduleName} \n";
        print "  action name             : {$this->actionName} \n";
        print "  action class name       : {$this->actionClassName} \n";
        print "  action dicon file name  : {$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        print "  action tpl file name    : {$this->actionName}.tpl \n";
        print "  service interface name  : {$this->serviceInterfaceName} \n";
        print "  service class name      : {$this->serviceClassName} \n";
        print "  service test class name : {$this->serviceClassName}Test \n";
        print "  service dicon file name : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";

        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function prepareFiles(){
        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareActionDiconFile();
        $this->prepareServiceClassFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDaoFile();
        $this->prepareDaoTestFile();
        $this->prepareEntityFile();
        $this->prepareServiceDiconFile();
    }

    private function prepareFilesWithCommonsDao(){
        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareActionDiconFile();
        $this->prepareServiceClassFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDaoTestFile();
        $this->prepareServiceDiconFile();
    }
    
    private function prepareFilesWithoutDao(){
        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareActionDiconFile();
        $this->prepareServiceClassFileWithoutDao();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareServiceDiconFileWithoutDao();
    }

    private function prepareActionFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_ACTION_DIR . 
                   "{$this->actionClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/action.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@SERVICE_INTERFACE@@/");
        $replacements = array($this->actionClassName,$this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareHtmlFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                     $this->moduleName . 
                     S2BASE_PHP5_VIEW_DIR . 
                     "{$this->actionName}" . 
                     S2Base_GenerateCommand::TPL_SUFFIX; 
        $htmlFile = defined('S2BASE_PHP5_LAYOUT') ? 'html_layout.php' : 'html.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/action/$htmlFile");
        $patterns = array("/@@MODULE_NAME@@/","/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareActionDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/action_dicon.php');
        $patterns = array("/@@MODULE_NAME@@/","/@@COMPONENT_NAME@@/","/@@CLASS_NAME@@/","/@@SERVICE_CLASS@@/");
        $replacements = array($this->moduleName,$this->actionName,$this->actionClassName,$this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }    

    private function prepareServiceClassFile(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_SERVICE_DIR . 
                   "{$this->serviceClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/","/@@DAO_NAME@@/");
        $replacements = array($this->serviceClassName,$this->serviceInterfaceName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceClassFileWithoutDao(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_SERVICE_DIR . 
                   "{$this->serviceClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_without_dao.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->serviceClassName,$this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_SERVICE_DIR . 
                   "{$this->serviceInterfaceName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR . 
                    $this->moduleName . 
                    S2BASE_PHP5_SERVICE_DIR . 
                    "$testName.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_test.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@SERVICE_CLASS@@/","/@@SERVICE_INTERFACE@@/");
        $replacements = array($testName,$this->moduleName,$this->serviceClassName,$this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareDaoFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DAO_DIR . 
                   "{$this->daoInterfaceName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/dao.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareDaoTestFile(){
        $testClassName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR . 
                    $this->moduleName . 
                    S2BASE_PHP5_DAO_DIR . 
                    "$testClassName.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/dao_test.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@DAO_CLASS@@/","/@@SERVICE_CLASS@@/");
        $replacements = array($testClassName,$this->moduleName,$this->daoInterfaceName,$this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareEntityFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_ENTITY_DIR . 
                   "{$this->entityClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/entity.php');
        $src = EntityCommand::getAccessorSrc($this->cols);

        $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/");
        $replacements = array($this->entityClassName,$this->tableName,$src);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_dicon.php');

        $patterns = array("/@@SERVICE_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array($this->serviceClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceDiconFileWithoutDao(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_dicon_without_dao.php');

        $patterns = array("/@@SERVICE_CLASS@@/");
        $replacements = array($this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>