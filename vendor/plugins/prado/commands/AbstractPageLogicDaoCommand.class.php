<?php
/**
 * page,logic,daoを生成します。
 * 
 * 生成ファイル
 * <ul>
 *   <li>app/modules/module名/logic/logic名.class.php</li>
 *   <li>app/modules/module名/logic/logic名Impl.class.php</li>
 *   <li>app/modules/module名/dicon/logic名Impl.dicon</li>
 *   <li>test/modules/module名/logic/logic名ImplTest.class.php</li>
 * </ul>
 * 
 */
abstract class AbstractPageLogicDaoCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $pageName;
    protected $pageClassName;
    protected $logicClassName;
    protected $logicInterfaceName;
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

            $pageName = S2Base_StdinManager::getValue('page name ? : ');
            $this->validate($pageName);

            $this->useDao = $this->isUseDao();
            if($this->useDao){
                $this->useCommonsDao = $this->isUseCommonsDao();
                if ($this->useCommonsDao) {
                    if ($this->getPageLogicDaoInfoWithCommonsDao($pageName) and
                        $this->finalConfirm()) {
                        $this->prepareFiles();
                    }
                } else {
                    $this->useDB = $this->isUseDB();
                    if ($this->useDB) {
                        if ($this->getPageLogicDaoInfoWithDB($pageName) and
                            $this->finalConfirm()) {
                            $this->prepareFiles();
                        }
                    } else {
                        if ($this->getPageLogicDaoInfoInteractive($pageName) and
                            $this->finalConfirm()) {
                            $this->prepareFiles();
                        }
                    }
                }
            } else {
                $this->setupPropertyWithoutDao($pageName);
                if ($this->finalConfirm()){
                    $this->prepareFiles();
                }
            }
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function getPageLogicDaoInfoWithCommonsDao($pageName){
        $daos = DaoCommand::getAllDaoFromCommonsDao();
        $daoName = S2Base_StdinManager::getValueFromArray($daos, "dao list");
        if(S2Base_CommandUtil::isListExitLabel($daoName)){
            return false;
        }
        $this->setupPropertyWithoutDao($pageName);
        $this->daoInterfaceName = $daoName;
        $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
        $this->tableName = 'auto defined';
        $this->cols = array('auto defined');

        return true;
    }

    protected function getPageLogicDaoInfoWithDB($pageName) {
        $this->setupPropertyWithDao($pageName);

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

    protected function getPageLogicDaoInfoInteractive($pageName) {
        $this->setupPropertyWithDao($pageName);

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

    protected function setupPropertyWithDao($pageName){
        $this->setupPropertyWithoutDao($pageName);
        $name = ucfirst($pageName);
        $this->daoInterfaceName = $name . "Dao";
        $this->entityClassName = $name . "Entity";
    }

    protected function setupPropertyWithoutDao($pageName){
        $this->pageName = $pageName;
        $name = ucfirst($pageName);
        //$this->pageClassName = $name . PageCommand::PAGE_CLASS_SUFFIX;
        $this->pageClassName = $name;
        $this->logicInterfaceName = $name . "Logic";
        $this->logicClassName = $name . "LogicImpl";
        $this->extendsEntityClassName = "none";
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print  PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name             : {$this->moduleName}" . PHP_EOL;
        print "  page name             : {$this->pageName}" . PHP_EOL;
        print "  page class name       : {$this->pageClassName}" . PHP_EOL;
        print "  page dicon file name  : {$this->pageClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  page template file    : {$this->pageName}" . S2BASE_PHP5_PRADO_PAGE_SUFFIX . PHP_EOL;
        print "  logic interface name  : {$this->logicInterfaceName}" . PHP_EOL;
        print "  logic class name      : {$this->logicClassName}" . PHP_EOL;
        print "  logic test class name : {$this->logicClassName}Test" . PHP_EOL;
        print "  logic dicon file name : {$this->logicClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
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
        $this->preparePageFile();
        $this->prepareTemplateFile();
        $this->preparePageDiconFile();
        $this->prepareLogicInterfaceFile();
        $this->prepareLogicTestFile();
        if ($this->useDao) {
            $this->prepareLogicDiconFile();
            $this->prepareLogicClassFile();
            $this->prepareDaoTestFile();
            if (!$this->useCommonsDao) {
                $this->prepareDaoFile();
                $this->prepareEntityFile();
            }
        } else {
            $this->prepareLogicClassFileWithoutDao();
            $this->prepareLogicDiconFileWithoutDao();
        }
    }

    protected function preparePageFile(){
        $srcFile = DOCUMENT_ROOT_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_PRADO_PAGES_DIR
                 . $this->pageClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/page.php');
        $logicProp = strtolower(substr($this->logicInterfaceName,0,1)) . substr($this->logicInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@LOGIC_INTERFACE@@/",
                          "/@@LOGIC_PROPERTY@@/");
        $replacements = array($this->pageClassName,
                              $this->logicInterfaceName,
                              $logicProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
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
                     . "/skeleton/page-logic-dao/$templateFile");
        $patterns = array("/@@MODULE_NAME@@/","/@@PAGE_NAME@@/");
        $replacements = array($this->moduleName,$this->pageName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function preparePageDiconFile(){
        $srcFile = DOCUMENT_ROOT_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_PRADO_DICON_DIR
                 . $this->pageClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/page_dicon.php');
        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@COMPONENT_NAME@@/",
                          "/@@CLASS_NAME@@/",
                          "/@@LOGIC_CLASS@@/");
        $replacements = array($this->moduleName,
                              $this->pageName,
                              $this->pageClassName,
                              $this->logicClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }    

    protected function prepareLogicClassFile(){
        $logicName = $this->logicClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_LOGIC_DIR
                 . $this->logicClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/logic.php');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1))
                 . substr($this->daoInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@INTERFACE_NAME@@/",
                          "/@@DAO_NAME@@/",
                          "/@@DAO_PROPERTY@@/");
        $replacements = array($this->logicClassName,
                              $this->logicInterfaceName,
                              $this->daoInterfaceName,
                              $daoProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareLogicClassFileWithoutDao(){
        $logicName = $this->logicClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_LOGIC_DIR
                 . $this->logicClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/logic_without_dao.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->logicClassName,$this->logicInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareLogicInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_LOGIC_DIR
                 . $this->logicInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/logic_interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->logicInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareLogicTestFile(){
        $testName = $this->logicClassName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_LOGIC_DIR 
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/logic_test.php');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@LOGIC_CLASS@@/",
                          "/@@LOGIC_INTERFACE@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->logicClassName,
                              $this->logicInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/dao.php');

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
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/dao_test.php');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@DAO_CLASS@@/",
                          "/@@LOGIC_CLASS@@/");
        $replacements = array($testClassName,
                              $this->moduleName,
                              $this->daoInterfaceName,
                              $this->logicClassName);
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
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/entity_extends.php');
            $patterns = array("/@@CLASS_NAME@@/",
                              "/@@ACCESSOR@@/",
                              "/@@EXTENDS_CLASS@@/",
                              "/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,
                                  $accessorSrc,
                                  $this->extendsEntityClassName,
                                  $toStringSrc);
        }else{
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/entity.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);     
    }

    protected function prepareLogicDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->logicClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/logic_dicon.php');

        $patterns = array("/@@LOGIC_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array($this->logicClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareLogicDiconFileWithoutDao(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->logicClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_PRADO
                     . '/skeleton/page-logic-dao/logic_dicon_without_dao.php');

        $patterns = array("/@@LOGIC_CLASS@@/");
        $replacements = array($this->logicClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
