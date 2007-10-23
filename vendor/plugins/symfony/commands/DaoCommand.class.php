<?php
class DaoCommand implements S2Base_GenerateCommand {
    protected $daoInterfaceName;
    protected $entityClassName;
    protected $cols;
    protected $useDB;
    protected $tableName;
    protected $tableNames;

    /**
     * daoインターフェイス名からentityクラス名を導出します。
     * 
     * @param string $daoInterfaceName daoインターフェイス名
     * @return string entityクラス名
     */
    public static function guessEntityName($daoInterfaceName){
        $patterns = array("/Dao$/");
        $replacements = array('Entity');
        $guess = preg_replace($patterns,$replacements,$daoInterfaceName);
        return $guess == $daoInterfaceName ? 
                         $daoInterfaceName . 'Entity' : 
                         $guess;
    }

    /**
     * @see S2Base_GenerateCommand::getName()
     */
    public function getName(){
        return "dao";
    }

    public function isAvailable(){
        return true;
    }

    /**
     * @see S2Base_GenerateCommand::execute()
     */
    public function execute(){

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

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function getDaoInfoFromDB(){
        $dbms = S2Base_CommandUtil::getS2DaoSkeletonDbms();
        $this->tableNames = S2Base_StdinManager::getValuesFromArray($dbms->getTables(),
                                                                  "table list");
        $this->tableName = $this->tableNames[0];
        if (S2Base_CommandUtil::isListExitLabel($this->tableName)){
            return false;
        }
        $this->cols = EntityCommand::getColumnsFromTables($dbms, $this->tableNames);

        $this->daoInterfaceName = ucfirst(EntityCommand::getPropertyNameFromCol($this->tableName)) . S2DaoSkelConst::DaoName;
        $this->entityClassName = ucfirst(EntityCommand::getPropertyNameFromCol($this->tableName)) . S2DaoSkelConst::BeanName;
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

        $tableNameTmp = EntityCommand::guessTableName($this->entityClassName);
        $this->tableName = S2Base_StdinManager::getValue("table name ? [{$tableNameTmp}] : ");
        if(trim($this->tableName) == ''){
            $this->tableName = $tableNameTmp;
        }
        $this->validate($this->tableName);

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
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  dao interface name   : {$this->daoInterfaceName} " . PHP_EOL;
        print "  dao test class name  : {$this->daoInterfaceName}Test " . PHP_EOL;
        print "  entity class name    : {$this->entityClassName} " . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareDaoFile();
        $this->prepareEntityFile();
        $this->prepareDaoTestFile();
    }

    protected function prepareDaoFile(){
        $srcFile = S2BASE_PHP5_ROOT
                 . S2BASE_PHP5_DS
                 . 'lib'
                 . S2BASE_PHP5_DAO_DIR
                 . S2BASE_PHP5_DS . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SF
                     . '/skeletons/dao/dao.tpl');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoTestFile(){
        $testName = $this->daoInterfaceName . "Test";
        $testDir = S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'test' .
                        S2BASE_PHP5_DS . 'unit';
        $srcFile = $testDir 
                 . S2BASE_PHP5_DAO_DIR
                 . S2BASE_PHP5_DS . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SF
                     . '/skeletons/dao/test.tpl');
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@DAO_CLASS@@/");
        $replacements = array($testName,
                              $this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareEntityFile(){
        $srcFile = S2BASE_PHP5_ROOT
                 . S2BASE_PHP5_DS
                 . 'lib'
                 . S2BASE_PHP5_ENTITY_DIR
                 . S2BASE_PHP5_DS . $this->entityClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $accessorSrc = EntityCommand::getAccessorSrc($this->cols);
        $toStringSrc = EntityCommand::getToStringSrc($this->cols);
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_SF
                 . '/skeletons/entity/entity.tpl');
        $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
        $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
