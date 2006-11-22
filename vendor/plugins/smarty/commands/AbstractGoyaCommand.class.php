<<<<<<< .working
<?php
abstract class AbstractGoyaCommand implements S2Base_GenerateCommand {

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

    abstract protected function isUseCommonsDao();

    abstract protected function isUseDB();

    abstract protected function isEntityExtends();

    abstract protected function isUseDao();

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
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
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function getGoyaInfoWithCommonsDao($actionName){
        $daos = DaoCommand::getAllDaoFromCommonsDao();
        $daoName = S2Base_StdinManager::getValueFromArray($daos, "dao list");
        if(S2Base_CommandUtil::isListExitLabel($daoName)){
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

        $dbms = S2Base_CommandUtil::getS2DaoSkeletonDbms();
        $this->tableName = S2Base_StdinManager::getValueFromArray($dbms->getTables(),
                                                                  "table list");
        if (S2Base_CommandUtil::isListExitLabel($this->tableName)){
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
            if (S2Base_CommandUtil::isListExitLabel($this->extendsEntityClassName)){
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
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print  PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name             : {$this->moduleName}" . PHP_EOL;
        print "  action name             : {$this->actionName}" . PHP_EOL;
        print "  action class name       : {$this->actionClassName}" . PHP_EOL;
        print "  action dicon file name  : {$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  action template file    : {$this->actionName}" . S2BASE_PHP5_SMARTY_TPL_SUFFIX . PHP_EOL;
        print "  service interface name  : {$this->serviceInterfaceName}" . PHP_EOL;
        print "  service class name      : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name : {$this->serviceClassName}Test" . PHP_EOL;
        print "  service dicon file name : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        if ($this->useDao) {
            print "  dao interface name      : {$this->daoInterfaceName}" . PHP_EOL;
            print "  dao test class name     : {$this->daoInterfaceName}Test" . PHP_EOL;
            print "  entity class name       : {$this->entityClassName}" . PHP_EOL;
            if (!$this->useCommonsDao) {
                if (!$this->useDB) {
                    print "  entity class extends    : {$this->extendsEntityClassName}" . PHP_EOL;
                }
                print "  table name              : {$this->tableName}" . PHP_EOL;
                $cols = implode(', ',$this->cols);
                print "  columns                 : $cols" . PHP_EOL;
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
            $this->prepareServiceDiconFile();
            $this->prepareServiceClassFile();
            $this->prepareDaoTestFile();
            if (!$this->useCommonsDao) {
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
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/action.php');
        $serviceProp = strtolower(substr($this->serviceInterfaceName,0,1)) . substr($this->serviceInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@SERVICE_INTERFACE@@/",
                          "/@@SERVICE_PROPERTY@@/");
        $replacements = array($this->actionClassName,
                              $this->serviceInterfaceName,
                              $serviceProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareHtmlFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . S2BASE_PHP5_SMARTY_TPL_SUFFIX; 
        $htmlFile = defined('S2BASE_PHP5_LAYOUT') ? 'html_layout.php' : 'html.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/action/$htmlFile");
        $patterns = array("/@@MODULE_NAME@@/","/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareActionDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->actionClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/action_dicon.php');
        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@COMPONENT_NAME@@/",
                          "/@@CLASS_NAME@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($this->moduleName,
                              $this->actionName,
                              $this->actionClassName,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }    

    protected function prepareServiceClassFile(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service.php');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1))
                 . substr($this->daoInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@INTERFACE_NAME@@/",
                          "/@@DAO_NAME@@/",
                          "/@@DAO_PROPERTY@@/");
        $replacements = array($this->serviceClassName,
                              $this->serviceInterfaceName,
                              $this->daoInterfaceName,
                              $daoProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceClassFileWithoutDao(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_without_dao.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->serviceClassName,$this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR 
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_test.php');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@SERVICE_CLASS@@/",
                          "/@@SERVICE_INTERFACE@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->serviceClassName,
                              $this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/dao.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoTestFile(){
        $testClassName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $testClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/dao_test.php');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@DAO_CLASS@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($testClassName,
                              $this->moduleName,
                              $this->daoInterfaceName,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
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
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/entity_extends.php');
            $patterns = array("/@@CLASS_NAME@@/",
                              "/@@ACCESSOR@@/",
                              "/@@EXTENDS_CLASS@@/",
                              "/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,
                                  $accessorSrc,
                                  $this->extendsEntityClassName,
                                  $toStringSrc);
        }else{
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/entity.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);     
    }

    protected function prepareServiceDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_dicon.php');

        $patterns = array("/@@SERVICE_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array($this->serviceClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceDiconFileWithoutDao(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_dicon_without_dao.php');

        $patterns = array("/@@SERVICE_CLASS@@/");
        $replacements = array($this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
=======
<?php
abstract class AbstractGoyaCommand implements S2Base_GenerateCommand {

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
    protected $tableNames;
    protected $cols;
    protected $useCommonsDao;
    protected $useDB;
    protected $useDao;

    abstract protected function isUseCommonsDao();

    abstract protected function isUseDB();

    abstract protected function isEntityExtends();

    abstract protected function isUseDao();

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
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
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function getGoyaInfoWithCommonsDao($actionName){
        $daos = DaoCommand::getAllDaoFromCommonsDao();
        $daoName = S2Base_StdinManager::getValueFromArray($daos, "dao list");
        if(S2Base_CommandUtil::isListExitLabel($daoName)){
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

        $dbms = S2Base_CommandUtil::getS2DaoSkeletonDbms();

        $this->tableNames = S2Base_StdinManager::getValuesFromArray($dbms->getTables(),
                                                                  "table list");
        $this->tableName = $this->tableNames[0];
        if (S2Base_CommandUtil::isListExitLabel($this->tableName)){
            return false;
        }
        $this->cols = EntityCommand::getColumnsFromTables($dbms, $this->tableNames);

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
            if (S2Base_CommandUtil::isListExitLabel($this->extendsEntityClassName)){
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
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print  PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name             : {$this->moduleName}" . PHP_EOL;
        print "  action name             : {$this->actionName}" . PHP_EOL;
        print "  action class name       : {$this->actionClassName}" . PHP_EOL;
        print "  action dicon file name  : {$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  action template file    : {$this->actionName}" . S2BASE_PHP5_SMARTY_TPL_SUFFIX . PHP_EOL;
        print "  service interface name  : {$this->serviceInterfaceName}" . PHP_EOL;
        print "  service class name      : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name : {$this->serviceClassName}Test" . PHP_EOL;
        print "  service dicon file name : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        if ($this->useDao) {
            print "  dao interface name      : {$this->daoInterfaceName}" . PHP_EOL;
            print "  dao test class name     : {$this->daoInterfaceName}Test" . PHP_EOL;
            print "  entity class name       : {$this->entityClassName}" . PHP_EOL;
            if (!$this->useCommonsDao) {
                if (!$this->useDB) {
                    print "  entity class extends    : {$this->extendsEntityClassName}" . PHP_EOL;
                }
                print "  table name              : {$this->tableName}" . PHP_EOL;
                print '  tables                  : ' . implode(', ',$this->tableNames) . PHP_EOL;
                print '  columns                 : ' . implode(', ',$this->cols) . PHP_EOL;
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
            $this->prepareServiceDiconFile();
            $this->prepareServiceClassFile();
            $this->prepareDaoTestFile();
            if (!$this->useCommonsDao) {
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
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/action.php');
        $serviceProp = strtolower(substr($this->serviceInterfaceName,0,1)) . substr($this->serviceInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@SERVICE_INTERFACE@@/",
                          "/@@SERVICE_PROPERTY@@/");
        $replacements = array($this->actionClassName,
                              $this->serviceInterfaceName,
                              $serviceProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareHtmlFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . S2BASE_PHP5_SMARTY_TPL_SUFFIX; 
        $htmlFile = defined('S2BASE_PHP5_LAYOUT') ? 'html_layout.php' : 'html.php';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . "/skeleton/action/$htmlFile");
        $patterns = array("/@@MODULE_NAME@@/","/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareActionDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->actionClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/action_dicon.php');
        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@COMPONENT_NAME@@/",
                          "/@@CLASS_NAME@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($this->moduleName,
                              $this->actionName,
                              $this->actionClassName,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }    

    protected function prepareServiceClassFile(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service.php');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1))
                 . substr($this->daoInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@INTERFACE_NAME@@/",
                          "/@@DAO_NAME@@/",
                          "/@@DAO_PROPERTY@@/");
        $replacements = array($this->serviceClassName,
                              $this->serviceInterfaceName,
                              $this->daoInterfaceName,
                              $daoProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceClassFileWithoutDao(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_without_dao.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->serviceClassName,$this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR 
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_test.php');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@SERVICE_CLASS@@/",
                          "/@@SERVICE_INTERFACE@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->serviceClassName,
                              $this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/dao.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoTestFile(){
        $testClassName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $testClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/dao_test.php');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@DAO_CLASS@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($testClassName,
                              $this->moduleName,
                              $this->daoInterfaceName,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
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
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/entity_extends.php');
            $patterns = array("/@@CLASS_NAME@@/",
                              "/@@ACCESSOR@@/",
                              "/@@EXTENDS_CLASS@@/",
                              "/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,
                                  $accessorSrc,
                                  $this->extendsEntityClassName,
                                  $toStringSrc);
        }else{
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/entity.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);     
    }

    protected function prepareServiceDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_dicon.php');

        $patterns = array("/@@SERVICE_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array($this->serviceClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceDiconFileWithoutDao(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SMARTY
                     . '/skeleton/goya/service_dicon_without_dao.php');

        $patterns = array("/@@SERVICE_CLASS@@/");
        $replacements = array($this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
>>>>>>> .merge-right.r0
