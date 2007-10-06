<?php
class PagerCommand extends AbstractGoyaCommand {
    const DTO_SUFFIX = 'Dto';
    private $dtoClassName;
    private $dtoSessionKey;

    public function getDtoClassName() {
        return $this->dtoClassName;
    }
    public function setDtoClassName($dtoClassName) {
        $this->dtoClassName = $dtoClassName;
    }

    public function getDtoSessionKey() {
        return $this->dtoSessionKey;
    }
    public function setDtoSessionKey($dtoSessionKey) {
        $this->dtoSessionKey = $dtoSessionKey;
    }

    public function getName(){
        return "goya pager";
    }

    public function isAvailable(){
        return true;
    }

    public function execute(){
        //$this->entityPropertyNames = array();
        parent::execute();
    }

    protected function isUseCommonsDao() {
        return false;
    }

    protected function isUseDB() {
        return true;
    }

    protected function isEntityExtends() {
        return EntityCommand::isCommonsEntityAvailable();
    }

    protected function isUseDao() {
        return true;
    }

    protected function getGoyaInfoWithCommonsDao($actionName){
        if(parent::getGoyaInfoWithCommonsDao($actionName)) {
            $this->dtoClassName = ucfirst($this->formatActionName) . self::DTO_SUFFIX;
            $this->dtoSessionKey = $this->formatActionName . self::DTO_SUFFIX;
            return true;
        }
        return false;
    }

    public function setupPropertyWithDao($actionName){
        parent::setupPropertyWithDao($actionName);
        $this->dtoClassName = ucfirst($this->formatActionName) . self::DTO_SUFFIX;
        $this->dtoSessionKey = $this->formatActionName . self::DTO_SUFFIX;
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name               : {$this->moduleName}" . PHP_EOL;
        print "  controller name           : {$this->controllerName}" . PHP_EOL;
        print "  action name               : {$this->actionName} " . PHP_EOL;

        print "  action method name        : {$this->actionMethodName}" . PHP_EOL;
        print "  action template file      : {$this->actionName}" . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX . PHP_EOL;
        print "  service class name        : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name   : {$this->serviceClassName}Test" . PHP_EOL;
        print "  condition dto class name  : {$this->dtoClassName}" . PHP_EOL;
        print "  condition dto session key : {$this->dtoSessionKey}" . PHP_EOL;
        if ($this->useDao) {
            print "  dao interface name        : {$this->daoInterfaceName}" . PHP_EOL;
            print "  dao test class name       : {$this->daoInterfaceName}Test" . PHP_EOL;
            print "  entity class name         : {$this->entityClassName}" . PHP_EOL;
            if (!$this->useCommonsDao) {
                if (!$this->useDB) {
                    print "  entity class extends      : {$this->extendsEntityClassName}" . PHP_EOL;
                }
                print "  table name                : {$this->tableName}" . PHP_EOL;
                print '  columns                   : ' . implode(', ',$this->cols) . PHP_EOL;
            }
        }
        return S2Base_StdinManager::isYes('confirm ?');
    }

    public function prepareFiles(){
        $this->appModuleDir  = S2BASE_PHP5_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->appCtlDir     = $this->appModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;
        $this->appViewDir    = $this->appModuleDir . S2BASE_PHP5_DS . 'views';
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;

        $this->prepareActionFile();
        $this->prepareValidateIniFile();
        $this->prepareServiceTestFile();
        $this->prepareConditionDtoFile();
        $this->prepareServiceClassFile();
        $this->prepareDaoTestFile();
        $this->prepareHtmlFile();
        if (!$this->useCommonsDao) {
            $this->prepareDaoFile();
            $this->prepareDaoSqlFile();
            $this->prepareEntityFile();
        }
        if ($this->useCommonsDao) {
            $this->showMethodDefinitionMessage();
        }
    }

    protected function insertActionMethod($srcFile, $tempAction) {
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
        if(!file_put_contents($srcFile, $tempContent, LOCK_EX)){
            S2Base_CommandUtil::showException(new Exception("Cannot write to file [ $srcFile ]"));
        } else {
            print "[INFO ] modify : $srcFile" . PHP_EOL;
        }
    }

    protected function prepareActionFile(){
        $srcFile = $this->appModuleDir
                 . S2BASE_PHP5_DS . 'controllers'
                 . S2BASE_PHP5_DS . $this->controllerClassFile . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                    . '/skeletons/pager/action.tpl');
        $serviceProp = strtolower(substr($this->serviceClassName,0,1)) . substr($this->serviceClassName,1);

        $patterns = array("/@@ACTION_METHOD_NAME@@/",
                          "/@@CONDITION_DTO_NAME@@/",
                          "/@@CONDITION_DTO_SESSION_KEY@@/",
                          "/@@SERVICE_PROPERTY@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($this->actionMethodName,
                              $this->dtoClassName,
                              $this->dtoSessionKey,
                              $serviceProp,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        ActionCommand::insertActionMethod($srcFile,$tempContent);
    }

    protected function prepareHtmlFile(){
        if (ModuleCommand::isStandardView()) {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . 'scripts'
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . $this->actionName . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        } else {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . $this->actionName . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        }
        $viewSuffix = ModuleCommand::getViewSuffixName();
        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeletons/module/html_header$viewSuffix.tpl");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeletons/pager/html$viewSuffix.tpl");
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

    protected function prepareServiceClassFile(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . S2BASE_PHP5_DS . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/pager/service.tpl');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@DAO_NAME@@/",
                          "/@@DAO_PROPERTY@@/",
                          "/@@CONDITION_DTO_NAME@@/");
        $replacements = array($this->serviceClassName,
                              $this->daoInterfaceName,
                              $daoProp,
                              $this->dtoClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_DAO_DIR
                 . S2BASE_PHP5_DS . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/pager/dao.tpl');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/","/@@CONDITION_DTO_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName, $this->dtoClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoSqlFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_DAO_DIR
                 . S2BASE_PHP5_DS . $this->daoInterfaceName
                 . '_findByConditionDtoList.sql';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/pager/dao_sql.tpl');

        $patterns = array("/@@TABLE_NAME@@/",
                          "/@@WHERE_CONDITION@@/");
        $replacements = array($this->tableName,
                              $this->getWhereCondition($this->cols));
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function getWhereCondition($cols) {
        foreach($cols as $col) {
            $conds[] = "      $col like /*dto.keywordLike*/'%%'";
        }
        return implode(' or' . PHP_EOL, $conds);
    }

    protected function prepareConditionDtoFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_ENTITY_DIR
                 . S2BASE_PHP5_DS . $this->dtoClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/pager/condition_dto.tpl');
        $patterns = array("/@@CONDITION_DTO_NAME@@/");
        $replacements = array($this->dtoClassName);
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
                      . '/skeletons/pager/validate.ini.tpl');
        $patterns = array("/@@ACTION_NAME@@/");
        $replacements = array($this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
