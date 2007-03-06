<?php
class ModelCommand implements S2Base_GenerateCommand {

    const MODEL_DIR = 'model';

    protected $moduleName;
    protected $controllerName;
    protected $modelInterfaceName;
    protected $modelClassName;
    protected $useDB;
    protected $tableName;
    protected $primaryKey;
    protected $tableNames;

    protected $srcModuleDir;
    protected $srcCtlDir;
    protected $testModuleDir;
    protected $testCtlDir;

    /**
     * @see S2Base_GenerateCommand::getName()
     */
    public function getName(){
        return "model";
    }

    /**
     * @see S2Base_GenerateCommand::execute()
     */
    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }
            $this->controllerName = ModuleCommand::getActionControllerName($this->moduleName);
            if(S2Base_CommandUtil::isListExitLabel($this->controllerName)){
                return;
            }

            $this->useDB = $this->isUseDB();
            if ($this->useDB) {
                if ($this->getModelInfoFromDB() and
                    $this->finalConfirm()){
                    $this->prepareFiles();
                }
            } else {
                if ($this->getModelInfoInteractive() and
                    $this->finalConfirm()){
                    $this->prepareFiles();
                }
            }
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function getModelInfoFromDB(){
        $dbms = S2Base_CommandUtil::getS2DaoSkeletonDbms();
        $pdo = S2ContainerFactory::create(PDO_DICON)->getComponent('dataSource')->getConnection();
        $tablesTmp = $dbms->getTables();
        $tables = array();
        foreach ($tablesTmp as $table) {
            $pks = S2Dao_DatabaseMetaDataUtil::getPrimaryKeySet($pdo,$table);
            if (count($pks) == 1) {
                $tables[] = $table;
            }
        }
        $this->tableName = S2Base_StdinManager::getValueFromArray($tables, "table list");
        if (S2Base_CommandUtil::isListExitLabel($this->tableName)){
            return false;
        }
        $pks = S2Dao_DatabaseMetaDataUtil::getPrimaryKeySet($pdo,$this->tableName);
        $this->primaryKey = $pks[0];

        $this->setupModelClassName();

        return true;
    }

    protected function getModelInfoInteractive(){
        $this->tableName = S2Base_StdinManager::getValue("table name ? : ");
        $this->validate($this->tableName);

        $this->primaryKey = 'id';
        $primaryKeyTmp = S2Base_StdinManager::getValue("primary key name ? [{$this->primaryKey}] : ");
        if(trim($primaryKeyTmp) != ''){
             $this->primaryKey = $primaryKeyTmp;
        }
        $this->validate($this->primaryKey);
        $this->setupModelClassName();

        return true;
    }
    
    protected function setupModelClassName(){
        $modelInterfaceNameTmp = ucfirst(ModuleCommand::formatModuleName($this->tableName))  . 'Model';

        $this->modelInterfaceName = S2Base_StdinManager::getValue("model interface name ? [{$modelInterfaceNameTmp}] : ");
        if(trim($this->modelInterfaceName) == ''){
             $this->modelInterfaceName = $modelInterfaceNameTmp;
        }
        $this->validate($this->modelInterfaceName);

        $modelClassNameTmp = $this->modelInterfaceName . 'Impl';
        $this->modelClassName = S2Base_StdinManager::getValue("model class name ? [{$modelClassNameTmp}] : ");
        if(trim($this->modelClassName) == ''){
             $this->modelClassName = $modelClassNameTmp;
        }
        $this->validate($this->modelClassName);
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name          : {$this->moduleName} " . PHP_EOL;
        print "  controller name      : {$this->controllerName} " . PHP_EOL;
        print "  table name           : {$this->tableName} " . PHP_EOL;
        print "  primary key          : {$this->primaryKey} " . PHP_EOL;
        print "  model interface name : {$this->modelInterfaceName} " . PHP_EOL;
        print "  model class name     : {$this->modelClassName} " . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->srcModuleDir  = S2BASE_PHP5_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->srcCtlDir     = $this->srcModuleDir . $this->controllerName . S2BASE_PHP5_DS;
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->testCtlDir    = $this->testModuleDir . $this->controllerName . S2BASE_PHP5_DS;

        S2Base_CommandUtil::createDirectory($this->srcCtlDir . self::MODEL_DIR);
        S2Base_CommandUtil::createDirectory($this->testCtlDir . self::MODEL_DIR);

        $this->prepareModelImplFile();
        $this->prepareModelInterfaceFile();
        $this->prepareModelTestFile();
        $this->prepareDiconFile();
    }

    protected function prepareModelImplFile(){
        $srcFile = $this->srcCtlDir
                 . self::MODEL_DIR
                 . S2BASE_PHP5_DS
                 . $this->modelClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/model/model.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@INTERFACE_NAME@@/",
                          "/@@TABLE_NAME@@/",
                          "/@@PRIMARY_KEY@@/");
        $replacements = array($this->modelClassName,
                              $this->modelInterfaceName,
                              $this->tableName,
                              $this->primaryKey);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareModelInterfaceFile(){
        $srcFile = $this->srcCtlDir
                 . self::MODEL_DIR
                 . S2BASE_PHP5_DS
                 . $this->modelInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/model/interface.tpl');
        $tempContent = preg_replace("/@@INTERFACE_NAME@@/",
                             $this->modelInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareModelTestFile(){
        $testName = $this->modelClassName . "Test";
        $srcFile = $this->testCtlDir
                 . self::MODEL_DIR
                 . S2BASE_PHP5_DS
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/model/test.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@MODEL_INTERFACE@@/",
                          "/@@MODEL_CLASS@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->controllerName,
                              $this->modelInterfaceName,
                              $this->modelClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDiconFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DICON_DIR
                 . $this->modelClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/model/dicon.tpl');
        $tempContent = preg_replace("/@@MODEL_CLASS@@/",
                                    $this->modelClassName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

}
?>
