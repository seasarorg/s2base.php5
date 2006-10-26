<?php
class GoyaCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $actionName;
    protected $actionClassName;
    protected $serviceClassName;
    protected $serviceInterfaceName;
    protected $daoInterfaceName;
    protected $entityClassName;
    protected $extendsEntityClassName;
    protected $entityExtends;
    protected $tableName;
    protected $cols;
    protected $useCommonsDao;
    protected $useDB;
    protected $useDao;

    public function getName(){
        return "goya";
    }

    public function execute(){
        try{
            $this->moduleName = DefaultCommandUtil::getModuleName();
            if(DefaultCommandUtil::isListExitLabel($this->moduleName)){
                return;
            }

            $actionName = S2Base_StdinManager::getValue('action name ? : ');
            $this->validate(actionName);

            $this->useDao = $this->isUseDao();
            if($this->useDao){
                $this->useCommonsDao = $this->isUseCommonsDao();
                if ($this->useCommonsDao) {
                    if ($this->getGoyaInfoWithCommonsDao($actionName) and
                        $this->finalConfirm()) {
                        $this->prepareFiles();
                    }
                } else {
                    $this->useDB = $this->isUseDB();
                    if ($this->useDB) {
                        if ($this->getGoyaInfoWithDB($actionName) and
                            $this->finalConfirm()) {
                            $this->prepareFiles();
                        }
                    } else {
                        if ($this->getGoyaInfoInteractive($actionName) and
                            $this->finalConfirm()) {
                            $this->prepareFiles();
                        }
                    }
                }
            } else {
                $this->setupPropertyWithoutDao($actionName);
                if ($this->finalConfirm()){
                    $this->prepareFiles();
                }
            }
        } catch(Exception $e) {
            DefaultCommandUtil::showException($e);
            return;
        }
    }

    protected function isUseCommonsDao() {
        return DaoCommand::isCommonsDaoAvailable();
    }

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function isEntityExtends() {
        return EntityCommand::isCommonsEntityAvailable();
    }

    protected function isUseDao() {
        return S2Base_StdinManager::isYes('use dao ?');
    }

    protected function getGoyaInfoWithCommonsDao($actionName){
        $daos = DaoCommand::getAllDaoFromCommonsDao();
        $daoName = S2Base_StdinManager::getValueFromArray($daos, "dao list");
        if(DefaultCommandUtil::isListExitLabel($daoName)){
            return false;
        }
        $this->setupPropertyWithoutDao($actionName);
        $this->daoInterfaceName = $daoName;
        $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
        $this->tableName = 'auto defined';
        $this->cols = array('auto defined');

        return true;
    }

    protected function getGoyaInfoWithDB($actionName) {
        $this->setupPropertyWithDao($actionName);

        $dbms = DefaultCommandUtil::getS2DaoSkeletonDbms();
        $this->tableName = S2Base_StdinManager::getValueFromArray($dbms->getTables(),
                                                                  "table list");
        if (DefaultCommandUtil::isListExitLabel($this->tableName)){
            return false;
        }
        $this->cols = $dbms->getColumns($this->tableName);
        $this->extendsEntityClassName = "none";

        $daoInterfaceNameTmp = S2Base_StdinManager::getValue("dao interface name [{$this->daoInterfaceName}]? : ");
        $this->daoInterfaceName = trim($daoInterfaceNameTmp) == '' ? $this->daoInterfaceName : $daoInterfaceNameTmp;
        $this->validate($this->daoInterfaceName);

        $entityClassNameTmp = S2Base_StdinManager::getValue("entity class name ? [{$this->entityClassName}] : ");
        $this->entityClassName = trim($entityClassNameTmp) == '' ? $this->entityClassName : $entityClassNameTmp;
        $this->validate($this->entityClassName);
        return true;
    }

    protected function getGoyaInfoInteractive($actionName) {
        $this->setupPropertyWithDao($actionName);

        $this->entityExtends = $this->isEntityExtends();
        if ($this->entityExtends) {
            $entitys = EntityCommand::getAllEntityFromCommonsDao();
            $this->extendsEntityClassName = S2Base_StdinManager::getValueFromArray($entitys,
                                                    "entity list");
            if (DefaultCommandUtil::isListExitLabel($this->extendsEntityClassName)){
                return false;
            }
            $this->tableName = "extended";
        } else {
            $tableNameTmp = EntityCommand::guessTableName($this->entityClassName);
            $this->tableName = S2Base_StdinManager::getValue("table name ? [{$tableNameTmp}] : ");
            if(trim($this->tableName) == ''){
                $this->tableName = $tableNameTmp;
            }
            $this->validate($this->tableName);
        }

        $cols = S2Base_StdinManager::getValue("columns ? (id,name,--,,) : ");
        $this->cols = EntityCommand::validateCols($cols);

        return true;
    }

    protected function setupPropertyWithDao($actionName){
        $this->setupPropertyWithoutDao($actionName);
        $name = ucfirst($actionName);
        $this->daoInterfaceName = $name . "Dao";
        $this->entityClassName = $name . "Entity";
    }

    protected function setupPropertyWithoutDao($actionName){
        $this->actionName = $actionName;
        $name = ucfirst($actionName);
        $this->actionClassName = $name . ActionCommand::ACTION_CLASS_SUFFIX;
        $this->serviceInterfaceName = $name . "Service";
        $this->serviceClassName = $name . "ServiceImpl";
        $this->extendsEntityClassName = "none";
    }

    protected function validate($name){
        DefaultCommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name             : {$this->moduleName} \n";
        print "  action name             : {$this->actionName} \n";
        print "  action class name       : {$this->actionClassName} \n";
        print "  action dicon file name  : {$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        print "  action template file    : {$this->actionName}" . S2BASE_PHP5_SMARTY_TPL_SUFFIX . "\n";
        print "  service interface name  : {$this->serviceInterfaceName} \n";
        print "  service class name      : {$this->serviceClassName} \n";
        print "  service test class name : {$this->serviceClassName}Test \n";
        print "  service dicon file name : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        if ($this->useDao) {
            print "  dao interface name      : {$this->daoInterfaceName} \n";
            print "  dao test class name     : {$this->daoInterfaceName}Test \n";
            print "  entity class name       : {$this->entityClassName} \n";
            if (!$this->useCommonsDao) {
                if (!$this->useDB) {
                    print "  entity class extends    : {$this->extendsEntityClassName} \n";
                }
                print "  table name              : {$this->tableName} \n";
                $cols = implode(', ',$this->cols);
                print "  columns                 : $cols \n";
            }
        }
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareActionDiconFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        if ($this->useDao) {
            if ($this->useCommonsDao) {
                $this->prepareServiceClassFile();
                $this->prepareDaoTestFile();
                $this->prepareServiceDiconFile();
            } else {
                $this->prepareDaoFile();
                $this->prepareEntityFile();
            }
        } else {
            $this->prepareServiceClassFileWithoutDao();
            $this->prepareServiceDiconFileWithoutDao();
        }
    }

    protected function prepareActionFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_ACTION_DIR
                 . $this->actionClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/action.php');
        $serviceProp = strtolower(substr($this->serviceInterfaceName,0,1)) . substr($this->serviceInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/","/@@SERVICE_INTERFACE@@/","/@@SERVICE_PROPERTY@@/");
        $replacements = array($this->actionClassName,$this->serviceInterfaceName,$serviceProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareHtmlFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . S2BASE_PHP5_SMARTY_TPL_SUFFIX; 
        $htmlFile = defined('S2BASE_PHP5_LAYOUT') ? 'html_layout.php' : 'html.php';
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/action/$htmlFile");
        $patterns = array("/@@MODULE_NAME@@/","/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareActionDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->actionClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/action_dicon.php');
        $patterns = array("/@@MODULE_NAME@@/","/@@COMPONENT_NAME@@/","/@@CLASS_NAME@@/","/@@SERVICE_CLASS@@/");
        $replacements = array($this->moduleName,$this->actionName,$this->actionClassName,$this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }    

    protected function prepareServiceClassFile(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service.php');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/","/@@DAO_NAME@@/","/@@DAO_PROPERTY@@/");
        $replacements = array($this->serviceClassName,$this->serviceInterfaceName,$this->daoInterfaceName,$daoProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceClassFileWithoutDao(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_without_dao.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->serviceClassName,$this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR 
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_test.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@SERVICE_CLASS@@/","/@@SERVICE_INTERFACE@@/");
        $replacements = array($testName,$this->moduleName,$this->serviceClassName,$this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/dao.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoTestFile(){
        $testClassName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $testClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/dao_test.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@DAO_CLASS@@/","/@@SERVICE_CLASS@@/");
        $replacements = array($testClassName,$this->moduleName,$this->daoInterfaceName,$this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareEntityFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_ENTITY_DIR
                 . $this->entityClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $accessorSrc = EntityCommand::getAccessorSrc($this->cols);
        $toStringSrc = EntityCommand::getToStringSrc($this->cols);
        if ($this->entityExtends) {
            $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/entity_extends.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@ACCESSOR@@/","/@@EXTENDS_CLASS@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$accessorSrc,$this->extendsEntityClassName,$toStringSrc);
        }else{
            $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/entity.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);     
    }

    protected function prepareServiceDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_dicon.php');

        $patterns = array("/@@SERVICE_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array($this->serviceClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceDiconFileWithoutDao(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_dicon_without_dao.php');

        $patterns = array("/@@SERVICE_CLASS@@/");
        $replacements = array($this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
