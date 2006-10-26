<?php
require_once('DefaultCommandUtil.class.php');
class GoyaCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $serviceName;
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

    public function getName(){
        return "goya";
    }

    public function execute(){
        try{
            $this->moduleName = DefaultCommandUtil::getModuleName();
            if(DefaultCommandUtil::isListExitLabel($this->moduleName)){
                return;
            }

            $serviceName = S2Base_StdinManager::getValue('service name ? : ');
            $this->validate($serviceName);

            $this->useCommonsDao = $this->isUseCommonsDao();
            if($this->useCommonsDao){
                if ($this->getGoyaInfoWithCommonsDao($serviceName) and
                    $this->finalConfirm()){
                    $this->prepareFiles();
                }
            }else{
                $this->useDB = $this->isUseDB();
                if ($this->useDB) {
                    if ($this->getGoyaInfowithDB($serviceName) and
                        $this->finalConfirm()) {
                        $this->prepareFiles();
                    }
                } else {
                    if ($this->getGoyaInfoInteractive($serviceName) and
                        $this->finalConfirm()) {
                        $this->prepareFiles();
                    }
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

    protected function getGoyaInfoWithDB($serviceName) {
        $this->setupPropertyFromServiceName($serviceName);

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

    protected function getGoyaInfoInteractive($serviceName) {
        $this->setupPropertyFromServiceName($serviceName);

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

    protected function getGoyaInfoWithCommonsDao($serviceName){
        $daos = DaoCommand::getAllDaoFromCommonsDao();
        $daoName = S2Base_StdinManager::getValueFromArray($daos, "dao list");
        if(DefaultCommandUtil::isListExitLabel($daoName)){
            return false;
        }

        $this->setupPropertyFromServiceName($serviceName);
        $this->daoInterfaceName = $daoName;
        $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
        $this->extendsEntityClassName = "none";
        $this->tableName = 'auto defined';
        $this->cols = array('auto defined');
        return true;
    }

    protected function setupPropertyFromServiceName($serviceName){
        $this->serviceName = $serviceName;
        $name = ucfirst($serviceName);
        $this->serviceInterfaceName = $name . "Service";
        $this->serviceClassName = $name . "ServiceImpl";
        $this->daoInterfaceName = $name . "Dao";
        $this->entityClassName = $name . "Entity";
        $this->extendsEntityClassName = "none";
    }

    protected function validate($name){
        DefaultCommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name             : {$this->moduleName} \n";
        print "  service name            : {$this->serviceName} \n";
        print "  service interface name  : {$this->serviceInterfaceName} \n";
        print "  service class name      : {$this->serviceClassName} \n";
        print "  service test class name : {$this->serviceClassName}Test \n";
        print "  dao interface name      : {$this->daoInterfaceName} \n";
        print "  dao test class name     : {$this->daoInterfaceName}Test \n";
        print "  entity class name       : {$this->entityClassName} \n";
        if (!$this->useDB and !$this->useCommonsDao) {
            print "  entity class extends    : {$this->extendsEntityClassName} \n";
        }
        if (!$this->useCommonsDao) {
            print "  table name              : {$this->tableName} \n";
            $cols = implode(', ',$this->cols);
            print "  columns                 : $cols \n";
        }
        print "  dicon file name         : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareServiceClassFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDaoTestFile();
        $this->prepareDiconFile();

        if (!$this->useCommonsDao) {
            $this->prepareDaoFile();
            $this->prepareEntityFile();
        }
    }

    protected function prepareServiceClassFile(){
        $serviceName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/service.php');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/","/@@DAO_NAME@@/","/@@DAO_PROPERTY@@/");
        $replacements = array($this->serviceClassName,$this->serviceInterfaceName,$this->daoInterfaceName,$daoProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/service_interface.php');
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
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/service_test.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@SERVICE_INTERFACE@@/","/@@SERVICE_CLASS@@/");
        $replacements = array($testName,$this->moduleName,$this->serviceInterfaceName,$this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/dao.php');

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
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/dao_test.php');

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
            $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                         . 'entity/entity_extends.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@ACCESSOR@@/","/@@EXTENDS_CLASS@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$accessorSrc,$this->extendsEntityClassName,$toStringSrc);
        }else{
            $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                         . 'entity/entity.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);     
    }

    protected function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . "{$this->serviceClassName}"
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/dicon.php');

        $patterns = array("/@@SERVICE_NAME@@/","/@@SERVICE_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array( $this->serviceName,$this->serviceClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
