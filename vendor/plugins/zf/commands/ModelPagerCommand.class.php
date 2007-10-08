<?php
class ModelPagerCommand implements S2Base_GenerateCommand {
    const DTO_SUFFIX = 'Dto';
    protected $dtoClassName;
    protected $dtoSessionKey;

    protected $moduleName;
    protected $controllerName;
    protected $actionName;
    protected $actionMethodName;
    protected $formatActionName;
    protected $modelClassName;
    protected $tableName;
    protected $primaryKey;
    protected $cols;
    protected $viewFile;
    protected $useDB;

    protected $dispatcher;
    protected $controllerClassName;
    protected $controllerClassFile;

    protected $appModuleDir;
    protected $appCtlDir;
    protected $appViewDir;
    protected $testModuleDir;
    protected $testCtlDir;

    public function __construct(){
        $this->dispatcher = new S2Base_ZfDispatcherImpl();
    }

    /**
     * @see S2Base_GenerateCommand::getName()
     */
    public function getName(){
        return "model pager";
    }

    /**
     * @see S2Base_GenerateCommand::isAvailable()
     */
    public function isAvailable(){
        return true;
    }

    public function execute(){
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
            return;
        }
        $this->controllerName = ModuleCommand::getActionControllerName($this->moduleName);
        if(S2Base_CommandUtil::isListExitLabel($this->controllerName)){
            return;
        }
        list($this->controllerName, $this->controllerClassName, $this->controllerClassFile) = 
            ModuleCommand::getControllerNames($this->dispatcher, $this->moduleName, $this->controllerName);
        $this->actionName = S2Base_StdinManager::getValue('action name ? : ');
        $this->formatActionName = $this->dispatcher->formatName($this->actionName);
        $this->validate($this->formatActionName);
        $this->actionMethodName = $this->dispatcher->formatActionName($this->actionName);
        $this->validate($this->actionMethodName);
        $this->viewFile = ActionCommand::getViewFileFromAction($this->actionName);
        $this->dtoClassName = ucfirst($this->formatActionName) . self::DTO_SUFFIX;
        $this->dtoSessionKey = $this->formatActionName . self::DTO_SUFFIX;

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
        $this->cols = $dbms->getColumns($this->tableName);

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
        $cols = S2Base_StdinManager::getValue("columns ? (id,name,--,,) : ");
        $this->cols = EntityCommand::validateCols($cols);

        return true;
    }

    protected function setupModelClassName(){
        $modelClassNameTmp = ucfirst(EntityCommand::getPropertyNameFromCol($this->tableName)) . ModelCommand::MODEL_SUFFIX;
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
        print  PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name               : {$this->moduleName}" . PHP_EOL;
        print "  controller name           : {$this->controllerName}" . PHP_EOL;
        print "  action name               : {$this->actionName}" . PHP_EOL;
        print "  format action name        : {$this->formatActionName}" . PHP_EOL;
        print "  action method name        : {$this->actionMethodName}" . PHP_EOL;
        print "  action template file      : {$this->viewFile}" . PHP_EOL;
        print "  model class name          : {$this->modelClassName}" . PHP_EOL;
        print "  model test class name     : {$this->modelClassName}Test" . PHP_EOL;
        print "  condition dto class name  : {$this->dtoClassName}" . PHP_EOL;
        print "  condition dto session key : {$this->dtoSessionKey}" . PHP_EOL;
        print "  table name                : {$this->tableName}" . PHP_EOL;
        print '  columns                   : ' . implode(', ',$this->cols) . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->appModuleDir  = S2BASE_PHP5_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->appCtlDir     = $this->appModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;
        $this->appViewDir    = $this->appModuleDir . S2BASE_PHP5_DS . 'views';
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;

        $this->prepareActionFile();
        $this->prepareValidateIniFile();
        $this->prepareModelClassFile();
        $this->prepareModelTestFile();
        $this->prepareHtmlFile();
        $this->prepareConditionDtoFile();
    }

    protected function prepareActionFile(){
        $srcFile = $this->appModuleDir
                 . S2BASE_PHP5_DS . 'controllers'
                 . S2BASE_PHP5_DS . $this->controllerClassFile . S2BASE_PHP5_CLASS_SUFFIX;
        $modelProp = strtolower(substr($this->modelClassName,0,1)) . substr($this->modelClassName,1);
        $tempAction = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                        . '/skeletons/model-pager/action.tpl');
        $patterns = array("/@@ACTION_METHOD_NAME@@/",
                          "/@@CONDITION_DTO_NAME@@/",
                          "/@@CONDITION_DTO_SESSION_KEY@@/",
                          "/@@TEMPLATE_NAME@@/",
                          "/@@MODEL_CLASS@@/",
                          "/@@MODEL_PROPERTY@@/");
        $replacements = array($this->actionMethodName,
                              $this->dtoClassName,
                              $this->dtoSessionKey,
                              $this->actionName . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX,
                              $this->modelClassName,
                              $modelProp);
        $tempAction = preg_replace($patterns,$replacements,$tempAction);
        ActionCommand::insertActionMethod($srcFile, $tempAction);
    }

    protected function prepareHtmlFile(){
        if (ModuleCommand::isStandardView()) {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . 'scripts'
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . $this->viewFile; 
        } else {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . $this->viewFile; 
        }
        $viewSuffix = ModuleCommand::getViewSuffixName();
        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeletons/module/html_header$viewSuffix.tpl");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeletons/model-pager/html$viewSuffix.tpl");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeletons/module/html_footer.tpl");
        }

        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@ACTION_NAME@@/",
                          "/@@PROPERTY_ROWS_TITLE@@/",
                          "/@@PROPERTY_ROWS@@/");
        $replacements = array($this->moduleName,
                              $this->controllerName,
                              $this->actionName,
                              $this->getPropertyRowsTitle(),
                              $this->getPropertyRowsHtml(ModuleCommand::isStandardView()));
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareValidateIniFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_DS . ModuleCommand::VALIDATE_DIR
                 . S2BASE_PHP5_DS . $this->actionName . '.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/action/validate.ini.tpl');
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . '/skeletons/model-pager/validate.ini.tpl');
        $patterns = array("/@@ACTION_NAME@@/");
        $replacements = array($this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function getPropertyRowsTitle() {
        $src = '<tr>' . PHP_EOL;
        foreach ($this->cols as $prop) {
            $src .= '<th>';
            $src .= ucfirst($prop);
            $src .= '</th>' . PHP_EOL;
        }
        return $src . '</tr>' . PHP_EOL;
    }

    protected function getPropertyRowsHtml($isStdView = false) {
        $src = '<tr>' . PHP_EOL;
        foreach ($this->cols as $prop) {
            $src .= '<td>';
            if ($isStdView) {
                $src .= '<?php echo $this->escape($row->' . $prop . '); ?>';
            } else {
                $src .= '{$row->' . $prop . '|escape}';
            }
            $src .= '</td>' . PHP_EOL;
        }
        return $src . '</tr>' . PHP_EOL;
    }

    protected function prepareConditionDtoFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_ENTITY_DIR
                 . S2BASE_PHP5_DS . $this->dtoClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/model-pager/condition_dto.tpl');
        $patterns = array("/@@CONDITION_DTO_NAME@@/");
        $replacements = array($this->dtoClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareModelClassFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_DS . ModuleCommand::MODEL_DIR
                 . S2BASE_PHP5_DS . $this->modelClassName . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/model-pager/model.tpl');

        $patterns = array("/@@MODEL_CLASS@@/",
                          "/@@TABLE_NAME@@/",
                          "/@@PRIMARY_KEY@@/",
                          "/@@CONDITION_DTO_NAME@@/",
                          "/@@WHERE_CLAUSE@@/");
        $replacements = array($this->modelClassName,
                              $this->tableName,
                              $this->primaryKey,
                              $this->dtoClassName,
                              $this->getWhereClause());
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function getWhereClause() {
        $contents = array();
        for($i=0;$i<count($this->cols); $i++){
            if ($i === 0) {
                $contents[] = '            $select->where(\'' . $this->cols[$i] . ' like ?\', $dto->getKeywordLike());';
            } else {
                $contents[] = '            $select->orWhere(\'' . $this->cols[$i] . ' like ?\', $dto->getKeywordLike());';
            }
        }
        return implode(PHP_EOL, $contents);
    }

    protected function prepareModelTestFile(){
        $testName = $this->modelClassName . "Test";
        $srcFile = $this->testCtlDir
                 . S2BASE_PHP5_DS . ModuleCommand::MODEL_DIR
                 . S2BASE_PHP5_DS . $testName . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/model/test.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@MODEL_CLASS@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->controllerName,
                              $this->modelClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
