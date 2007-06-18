<?php
abstract class AbstractGoyaCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $controllerName;
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
    protected $controllerClassFile;
    protected $ctlServiceInterfaceName;

    protected $srcModuleDir;
    protected $srcCtlDir;
    protected $testModuleDir;
    protected $testCtlDir;

    protected $entityPropertyNames;

    public function __construct(){
        $this->dispatcher = new S2Base_ZfDispatcher();
    }

    abstract protected function isUseCommonsDao();

    abstract protected function isUseDB();

    abstract protected function isEntityExtends();

    abstract protected function isUseDao();

    public function execute(){
        try{
            $this->entityPropertyNames = array();
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

            $this->ctlServiceInterfaceName = ModuleCommand::getCtlServiceInterfaceName($this->controllerName);
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
        $this->entityPropertyNames = $this->getEntityPropertyNames($this->entityClassName);

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

        $this->daoInterfaceName = ucfirst(EntityCommand::getPropertyNameFromCol($this->tableName)) . S2DaoSkelConst::DaoName;
        $this->entityClassName  = ucfirst(EntityCommand::getPropertyNameFromCol($this->tableName)) . S2DaoSkelConst::BeanName;
        $this->extendsEntityClassName = "none";

        $daoInterfaceNameTmp = S2Base_StdinManager::getValue("dao interface name [{$this->daoInterfaceName}]? : ");
        $this->daoInterfaceName = trim($daoInterfaceNameTmp) == '' ? $this->daoInterfaceName : $daoInterfaceNameTmp;
        $this->validate($this->daoInterfaceName);

        $entityClassNameTmp = S2Base_StdinManager::getValue("entity class name ? [{$this->entityClassName}] : ");
        $this->entityClassName = trim($entityClassNameTmp) == '' ? $this->entityClassName : $entityClassNameTmp;
        $this->validate($this->entityClassName);

        $this->mergeEntityPropertyNamesFromCols();
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
            $this->entityPropertyNames = $this->getEntityPropertyNames($this->extendsEntityClassName);
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
        $this->mergeEntityPropertyNamesFromCols();

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

    protected function getEntityPropertyNames($entityClassName) {
        $beanDesc = S2Container_BeanDescFactory::getBeanDesc(new ReflectionClass($entityClassName));
        $c = $beanDesc->getPropertyDescSize();
        $props = array();
        for($i=0; $i<$c; $i++){
            $props[] = $beanDesc->getPropertyDesc($i)->getPropertyName();
        }
        return $props;
    }

    public function mergeEntityPropertyNamesFromCols() {
        foreach ($this->cols as $col) {
            array_push($this->entityPropertyNames,
                       EntityCommand::getPropertyNameFromCol($col));
        }
        $this->entityPropertyNames = array_unique($this->entityPropertyNames);
    }

    protected function finalConfirm(){
        print  PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name             : {$this->moduleName}" . PHP_EOL;
        print "  controller name         : {$this->controllerName}" . PHP_EOL;
        print "  action name             : {$this->actionName}" . PHP_EOL;
        print "  format action name      : {$this->formatActionName}" . PHP_EOL;
        print "  action method name      : {$this->actionMethodName}" . PHP_EOL;
        print "  action dicon file name  : {$this->actionMethodName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  action template file    : {$this->actionName}" . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX . PHP_EOL;
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
        $this->srcModuleDir  = S2BASE_PHP5_MODULES_DIR . $this->moduleName;
        $this->srcCtlDir     = $this->srcModuleDir . S2BASE_PHP5_DS . $this->controllerName;
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . $this->moduleName;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . $this->controllerName;

        $this->prepareActionFile();
        $this->prepareActionDiconFile();
        $this->prepareValidateIniFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        if ($this->useDao) {
            $this->prepareHtmlFile();
            $this->prepareServiceDiconFile();
            $this->prepareServiceClassFile();
            $this->prepareDaoTestFile();
            if (!$this->useCommonsDao) {
                $this->prepareDaoFile();
                $this->prepareEntityFile();
            }
        } else {
            $this->prepareHtmlFileWithoutDao();
            $this->prepareServiceClassFileWithoutDao();
            $this->prepareServiceDiconFileWithoutDao();
        }
    }

    protected function prepareActionFile(){
        $srcFile = $this->srcModuleDir
                 . S2BASE_PHP5_DS
                 . $this->controllerClassFile
                 . S2BASE_PHP5_CLASS_SUFFIX;
        if ($this->useDao) {
            $tempAction = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                        . '/skeleton/goya/action.tpl');
        } else {
            $tempAction = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                        . '/skeleton/goya/action_without_dao.tpl');
        }
        $patterns = array("/@@ACTION_NAME@@/",
                          "/@@TEMPLATE_NAME@@/");
        $replacements = array($this->actionMethodName,
                              $this->actionName . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX);
        $tempAction = preg_replace($patterns,$replacements,$tempAction);
        ActionCommand::insertActionMethod($srcFile, $tempAction);
    }


    protected function prepareHtmlFile(){
        if (ModuleCommand::isStandardView()) {
            $srcFile = $this->srcCtlDir
                     . S2BASE_PHP5_VIEW_DIR
                     . S2BASE_PHP5_DS . 'scripts'
                     . S2BASE_PHP5_DS . $this->actionName . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        } else {
            $srcFile = $this->srcCtlDir
                     . S2BASE_PHP5_VIEW_DIR
                     . $this->actionName
                     . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX;
        }
        $viewSuffix = ModuleCommand::getViewSuffixName();
        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/module/html_header$viewSuffix.tpl");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeleton/goya/html$viewSuffix.tpl");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/module/html_footer.tpl");
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

    protected function prepareHtmlFileWithoutDao(){
        if (ModuleCommand::isStandardView()) {
            $srcFile = $this->srcCtlDir
                     . S2BASE_PHP5_VIEW_DIR
                     . S2BASE_PHP5_DS . 'scripts'
                     . S2BASE_PHP5_DS . $this->actionName . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        } else {
            $srcFile = $this->srcCtlDir
                     . S2BASE_PHP5_VIEW_DIR
                     . $this->actionName
                     . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX;
        }
        $viewSuffix = ModuleCommand::getViewSuffixName();
        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/module/html_header$viewSuffix.tpl");
        }

        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeleton/action/html$viewSuffix.tpl");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/module/html_footer.tpl");
        }

        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,
                              $this->controllerName,
                              $this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareActionDiconFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DICON_DIR
                 . $this->actionMethodName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/action_dicon.tpl');
        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@SERVICE_CLASS@@/",
                          "/@@CONTROLLER_CLASS_NAME@@/");
        $replacements = array($this->moduleName,
                              $this->controllerName,
                              $this->serviceClassName,
                              $this->controllerClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }    

    protected function prepareServiceClassFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/service.tpl');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);
        if ($this->serviceInterfaceName == $this->ctlServiceInterfaceName) {
            $implementsInterface = $this->serviceInterfaceName;
        } else {
            $implementsInterface = $this->serviceInterfaceName . ', ' . $this->ctlServiceInterfaceName;
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
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/service_without_dao.tpl');
        if ($this->serviceInterfaceName == $this->ctlServiceInterfaceName) {
            $implementsInterface = $this->serviceInterfaceName;
        } else {
            $implementsInterface = $this->serviceInterfaceName . ', ' . $this->ctlServiceInterfaceName;
        }
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->serviceClassName,$implementsInterface);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceInterfaceFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        if ($this->useDao) {
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                         . '/skeleton/goya/service_interface.tpl');
        } else {
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                         . '/skeleton/goya/service_interface_without_dao.tpl');
        }
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = $this->testCtlDir
                 . S2BASE_PHP5_SERVICE_DIR 
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/service_test.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@SERVICE_CLASS@@/",
                          "/@@SERVICE_INTERFACE@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->controllerName,
                              $this->serviceClassName,
                              $this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/dao.tpl');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoTestFile(){
        $testClassName = $this->daoInterfaceName . "Test";
        $srcFile = $this->testCtlDir
                 . S2BASE_PHP5_DAO_DIR
                 . $testClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/dao_test.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@DAO_CLASS@@/",
                          "/@@SERVICE_CLASS@@/");
        $replacements = array($testClassName,
                              $this->moduleName,
                              $this->controllerName,
                              $this->daoInterfaceName,
                              $this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareEntityFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_ENTITY_DIR
                 . $this->entityClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $accessorSrc = EntityCommand::getAccessorSrc($this->cols);
        $toStringSrc = EntityCommand::getToStringSrc($this->cols);
        if ($this->isEntityExtends) {
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/entity_extends.tpl');
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
                     . '/skeleton/goya/entity.tpl');
            $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);     
    }

    protected function prepareValidateIniFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DS
                 . ModuleCommand::VALIDATE_DIR
                 . S2BASE_PHP5_DS
                 . $this->actionName
                 . '.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/action/validate.ini.tpl');
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceDiconFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/service_dicon.tpl');

        $patterns = array("/@@SERVICE_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array($this->serviceClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceDiconFileWithoutDao(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/goya/service_dicon_without_dao.tpl');

        $patterns = array("/@@SERVICE_CLASS@@/");
        $replacements = array($this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function getPropertyRowsTitle() {
        $src = '<tr>' . PHP_EOL;
        foreach ($this->entityPropertyNames as $prop) {
            $src .= '<th>';
            $src .= ucfirst($prop);
            $src .= '</th>' . PHP_EOL;
        }
        return $src . '</tr>' . PHP_EOL;
    }

    protected function getPropertyRowsHtml($isStdView = false) {
        $src = '<tr>' . PHP_EOL;
        foreach ($this->entityPropertyNames as $prop) {
            $src .= '<td>';
            if ($isStdView) {
                $src .= '<?php echo $this->escape($row->get' . ucfirst($prop) . '()); ?>';
            } else {
                $src .= '{$row->get' . ucfirst($prop) . '()|escape}';
            }
            $src .= '</td>' . PHP_EOL;
        }
        return $src . '</tr>' . PHP_EOL;
    }

    public function getModuleName() {
        return $this->moduleName;
    }
    public function setModuleName($moduleName) {
        $this->moduleName = $moduleName;
    }

    public function getControllerName() {
        return $this->controllerName;
    }
    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    public function getActionName() {
        return $this->actionName;
    }
    public function setActionName($actionName) {
        $this->actionName = $actionName;
    }

    public function getActionMethodName() {
        return $this->actionMethodName;
    }
    public function setActionMethodName($actionMethodName) {
        $this->actionMethodName = $actionMethodName;
    }

    public function getFormatActionName() {
        return $this->formatActionName;
    }
    public function setFormatActionName($formatActionName) {
        $this->formatActionName = $formatActionName;
    }

    public function getServiceClassName() {
        return $this->serviceClassName;
    }
    public function setServiceClassName($serviceClassName) {
        $this->serviceClassName = $serviceClassName;
    }

    public function getServiceInterfaceName() {
        return $this->serviceInterfaceName;
    }
    public function setServiceInterfaceName($serviceInterfaceName) {
        $this->serviceInterfaceName = $serviceInterfaceName;
    }

    public function getDaoInterfaceName() {
        return $this->daoInterfaceName;
    }
    public function setDaoInterfaceName($daoInterfaceName) {
        $this->daoInterfaceName = $daoInterfaceName;
    }

    public function getEntityClassName() {
        return $this->entityClassName;
    }
    public function setEntityClassName($entityClassName) {
        $this->entityClassName = $entityClassName;
    }

    public function getExtendsEntityClassName() {
        return $this->extendsEntityClassName;
    }
    public function setExtendsEntityClassName($extendsEntityClassName) {
        $this->extendsEntityClassName = $extendsEntityClassName;
    }

    public function getEntityExtends() {
        return $this->entityExtends;
    }
    public function setEntityExtends($entityExtends) {
        $this->entityExtends = $entityExtends;
    }

    public function getTableName() {
        return $this->tableName;
    }
    public function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    public function getTableNames() {
        return $this->tableNames;
    }
    public function setTableNames($tableNames) {
        $this->tableNames = $tableNames;
    }

    public function getCols() {
        return $this->cols;
    }
    public function setCols($cols) {
        $this->cols = $cols;
    }

    public function getUseCommonsDao() {
        return $this->useCommonsDao;
    }
    public function setUseCommonsDao($useCommonsDao) {
        $this->useCommonsDao = $useCommonsDao;
    }

    public function getUseDB() {
        return $this->useDB;
    }
    public function setUseDB($useDB) {
        $this->useDB = $useDB;
    }

    public function getUseDao() {
        return $this->useDao;
    }
    public function setUseDao($useDao) {
        $this->useDao = $useDao;
    }

    public function getDispatcher() {
        return $this->dispatcher;
    }
    public function setDispatcher($dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function getControllerClassName() {
        return $this->controllerClassName;
    }
    public function setControllerClassName($controllerClassName) {
        $this->controllerClassName = $controllerClassName;
    }

    public function getControllerClassFile() {
        return $this->controllerClassFile;
    }
    public function setControllerClassFile($controllerClassFile) {
        $this->controllerClassFile = $controllerClassFile;
    }

    public function getCtlServiceInterfaceName() {
        return $this->ctlServiceInterfaceName;
    }
    public function setCtlServiceInterfaceName($ctlServiceInterfaceName) {
        $this->ctlServiceInterfaceName = $ctlServiceInterfaceName;
    }

    public function getSrcModuleDir() {
        return $this->srcModuleDir;
    }
    public function setSrcModuleDir($srcModuleDir) {
        $this->srcModuleDir = $srcModuleDir;
    }

    public function getSrcCtlDir() {
        return $this->srcCtlDir;
    }
    public function setSrcCtlDir($srcCtlDir) {
        $this->srcCtlDir = $srcCtlDir;
    }

    public function getTestModuleDir() {
        return $this->testModuleDir;
    }
    public function setTestModuleDir($testModuleDir) {
        $this->testModuleDir = $testModuleDir;
    }

    public function getTestCtlDir() {
        return $this->testCtlDir;
    }
    public function setTestCtlDir($testCtlDir) {
        $this->testCtlDir = $testCtlDir;
    }

    public function setEntityPropertyNames($entityPropertyNames) {
        $this->entityPropertyNames = $entityPropertyNames;
    }
}
?>
