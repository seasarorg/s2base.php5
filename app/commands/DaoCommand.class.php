<?php
require_once('DefaultCommandUtil.class.php');
class DaoCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $daoInterfaceName;
    protected $entityClassName;
    protected $extendsEntityClassName;
    protected $entityExtends;
    protected $cols;
    protected $useCommonsDao;
    protected $useDB;

    public static function guessEntityName($daoInterfaceName){
        $patterns = array("/Dao$/");
        $replacements = array('Entity');
        $guess = preg_replace($patterns,$replacements,$daoInterfaceName);
        return $guess == $daoInterfaceName ? 
                         $daoInterfaceName . 'Entity' : 
                         $guess;
    }

    public static function isCommonsDaoAvailable() {
        $daos = self::getAllDaoFromCommonsDao();
        if(count($daos) > 0){
            return S2Base_StdinManager::isYes('use commons dao ?');
        } else {
            return false;
        }
    }

    public static function getAllDaoFromCommonsDao(){
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
        return $daos;
    }

    public function getName(){
        return "dao";
    }

    public function execute(){
        try{
            $this->moduleName = DefaultCommandUtil::getModuleName();
            if(DefaultCommandUtil::isListExitLabel($this->moduleName)){
                return;
            }

            $this->useCommonsDao = $this->isUseCommonsDao();
            if($this->useCommonsDao){
                if ($this->getDaoInfoFromCommonsDao(self::getAllDaoFromCommonsDao()) and
                    $this->finalConfirm()){
                    $this->prepareFiles();
                }
            } else {
                $this->useDB = $this->isUseDB();
                if ($this->useDB) {
                    if ($this->getDaoInfoFromDB() and
                        $this->finalConfirm()){
                        $this->prepareFiles();
                    }
                } else {
                    if ($this->getDaoInfoInteractive() and
                        $this->finalConfirm()){
                        $this->prepareFiles();
                    }
                }
            }
        } catch(Exception $e) {
            DefaultCommandUtil::showException($e);
            return;
        }
    }

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function isEntityExtends() {
        return EntityCommand::isCommonsEntityAvailable();
    }

    protected function isUseCommonsDao() {
        return DaoCommand::isCommonsDaoAvailable();
    }

    protected function getDaoInfoFromCommonsDao($daos){
        $daoName = S2Base_StdinManager::getValueFromArray($daos, "dao list");
        if (DefaultCommandUtil::isListExitLabel($daoName)){
            return false;
        }
        $this->daoInterfaceName = $daoName;
        $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
        $this->extendsEntityClassName = "none";
        $this->tableName = 'auto defined';
        $this->cols = array('auto defined');
        return true;
    }

    protected function getDaoInfoFromDB(){
        $dbms = DefaultCommandUtil::getS2DaoSkeletonDbms();
        $this->tableName = S2Base_StdinManager::getValueFromArray($dbms->getTables(),
                                                                  "table list");
        if (DefaultCommandUtil::isListExitLabel($this->tableName)){
            return false;
        }
        $this->daoInterfaceName = ucfirst(strtolower($this->tableName)) . S2DaoSkelConst::DaoName;
        $this->entityClassName = ucfirst(strtolower($this->tableName)) . S2DaoSkelConst::BeanName;
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

    protected function getDaoInfoInteractive(){
        $this->daoInterfaceName = S2Base_StdinManager::getValue('dao interface name ? : ');
        $this->validate($this->daoInterfaceName);

        $entityClassNameTmp = self::guessEntityName($this->daoInterfaceName);
        $this->entityClassName = S2Base_StdinManager::getValue("entity class name ? [$entityClassNameTmp] : ");
        if(trim($this->entityClassName) == ''){
            $this->entityClassName = $entityClassNameTmp;
        }
        $this->validate($this->entityClassName);

        $this->entityExtends = $this->isEntityExtends();
        $this->extendsEntityClassName = "none";
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

    protected function getCmdMessage(){
        if ($this->entityClassName == null){
            return 'entity class name ? : ';
        }else{
            return "entity class name ? [$this->entityClassName] : ";
        }
    }

    protected function validate($name){
        DefaultCommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name          : {$this->moduleName} \n";
        print "  dao interface name   : {$this->daoInterfaceName} \n";
        print "  dao test class name  : {$this->daoInterfaceName}Test \n";
        print "  entity class name    : {$this->entityClassName} \n";
        if (!$this->useDB and !$this->useCommonsDao) {
            print "  entity class extends : {$this->extendsEntityClassName} \n";
        }
        if (!$this->useCommonsDao) {
            print "  table name           : {$this->tableName} \n";
            $cols = implode(', ',$this->cols);
            print "  columns              : $cols \n";
        }
        print "  dao dicon file name  : {$this->daoInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareDaoFile();
        $this->prepareEntityFile();
        if (!$this->useCommonsDao) {
            $this->prepareDaoTestFile();
            $this->prepareDiconFile();
        }
    }

    protected function prepareDaoFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName 
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dao/dao.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoTestFile(){
        $testName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName 
                 . S2BASE_PHP5_DAO_DIR
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dao/test.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@DAO_CLASS@@/");
        $replacements = array($testName,$this->moduleName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dao/dicon.php');
        $tempContent = preg_replace("/@@DAO_CLASS@@/",
                                    $this->daoInterfaceName,
                                    $tempContent);   
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
}
?>
