<?php
abstract class AbstractGoyaCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $actionName;
    protected $actionMethodName;
    protected $formatActionName;
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

    protected $dispatcher;
    protected $controllerClassName;
    protected $moduleServiceInterfaceName;

    public function __construct(){
        require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfDispatcher.php';
        $this->dispatcher = new S2Base_ZfDispatcher();
    }

    abstract protected function isUseCommonsDao();

    abstract protected function isUseDB();

    abstract protected function isEntityExtends();

    abstract protected function isUseDao();

    public function execute(){
        try{
            $this->moduleName = $this->getActionControllerName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }

            $this->controllerClassName = $this->dispatcher->formatControllerName($this->moduleName);
            $this->moduleServiceInterfaceName = ModuleCommand::getModuleServiceInterfaceName($this->moduleName);

            $this->actionName = S2Base_StdinManager::getValue('action name ? : ');
            $this->formatActionName = $this->dispatcher->formatName($this->actionName);
            $this->validate($this->formatActionName);
            $this->actionMethodName = $this->dispatcher->formatActionName($this->actionName);
            $this->validate($this->actionMethodName);

            $this->useDao = $this->isUseDao();
            if($this->useDao){
                $this->useCommonsDao = $this->isUseCommonsDao();
                if ($this->useCommonsDao) {
                    if ($this->getGoyaInfoWithCommonsDao($this->actionName) and
                        $this->finalConfirm()) {
                        $this->prepareFiles();
                    }
                } else {
                    $this->useDB = $this->isUseDB();
                    if ($this->useDB) {
                        if ($this->getGoyaInfoWithDB($this->actionName) and
                            $this->finalConfirm()) {
                            $this->prepareFiles();
                        }
                    } else {
                        if ($this->getGoyaInfoInteractive($this->actionName) and
                            $this->finalConfirm()) {
                            $this->prepareFiles();
                        }
                    }
                }
            } else {
                $this->setupPropertyWithoutDao($this->actionName);
                if ($this->finalConfirm()){
                    $this->prepareFiles();
                }
            }
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function getActionControllerName(){
        $modules = S2Base_CommandUtil::getAllModules();
        if(count($modules) == 0){
            throw new Exception("Module not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,'Controller list');
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
        $name = ucfirst($this->formatActionName);
        $this->daoInterfaceName = $name . "Dao";
        $this->entityClassName = $name . "Entity";
    }

    protected function setupPropertyWithoutDao($actionName){
        $name = ucfirst($this->formatActionName);
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
        print "  format action name      : {$this->formatActionName}" . PHP_EOL;
        print "  action method name      : {$this->actionMethodName}" . PHP_EOL;
        print "  action dicon file name  : {$this->actionMethodName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  action template file    : {$this->actionName}" . S2BASE_PHP5_ZF_TPL_SUFFIX . PHP_EOL;
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
                 . $this->controllerClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempAction = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                    . '/skeleton/goya/action.php');

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
        $patterns = array("/@@MODULE_NAME@@/","/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareActionDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->actionMethodName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/action_dicon.php');
        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@SERVICE_CLASS@@/",
                          "/@@CONTROLLER_CLASS_NAME@@/");
        $replacements = array($this->moduleName,
                              $this->serviceClassName,
                              $this->controllerClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }    

    protected function prepareServiceClassFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/service.php');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);
        if ($this->serviceInterfaceName == $this->moduleServiceInterfaceName) {
            $implementsInterface = $this->serviceInterfaceName;
        } else {
            $implementsInterface = $this->serviceInterfaceName . ', ' . $this->moduleServiceInterfaceName;
        }
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@INTERFACE_NAME@@/",
                          "/@@DAO_NAME@@/",
                          "/@@DAO_PROPERTY@@/");
        $replacements = array($this->serviceClassName,
                              $implementsInterface,
                              $this->daoInterfaceName,
                              $daoProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceClassFileWithoutDao(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/service_without_dao.php');
        if ($this->serviceInterfaceName == $this->moduleServiceInterfaceName) {
            $implementsInterface = $this->serviceInterfaceName;
        } else {
            $implementsInterface = $this->serviceInterfaceName . ', ' . $this->moduleServiceInterfaceName;
        }
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->serviceClassName,$implementsInterface);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
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
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
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
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
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
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
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
        if ($this->isEntityExtends) {
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
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
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
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
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
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
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/service_dicon_without_dao.php');

        $patterns = array("/@@SERVICE_CLASS@@/");
        $replacements = array($this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
