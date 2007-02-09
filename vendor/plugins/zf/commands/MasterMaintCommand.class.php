<?php
require_once('ModuleCommand.class.php');
require_once('PagerCommand.class.php');
require_once('ScaffoldCommand.class.php');
class MasterMaintCommand implements S2Base_GenerateCommand {
    private $existsControllers;
    private $dbms;
    private $pdo;
    private $moduleCommand;
    private $pagerCommand;
    private $scaffoldCommand;
    private $tableNames;
    private $controllerName;
    
    public function getName(){
        return "master maintenance";
    }

    public function execute(){
        try{
            $this->dbms = S2Base_CommandUtil::getS2DaoSkeletonDbms();
            $this->pdo = S2ContainerFactory::create(PDO_DICON)->getComponent('dataSource')->getConnection();
            $this->moduleCommand = new ModuleCommand();
            $this->pagerCommand = new PagerCommand();
            $this->scaffoldCommand = new ScaffoldCommand();

            if (S2BASE_PHP5_ZF_USE_MODULE) {
                $this->moduleName = S2Base_CommandUtil::getModuleName();
                if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                    return;
                }
            } else {
                $this->moduleName = S2BASE_PHP5_ZF_DEFAULT_MODULE;
                $this->validate($this->moduleName);
            }
            $this->existsControllers = ModuleCommand::getAllControllers($this->moduleName);
            $this->tableNames = $this->dbms->getTables();
            if (count($this->tableNames) < 1) {
                throw new Exception('table not found at all.');
            }
            if($this->finalConfirm() === false) {
                return;
            }

            $controllers = array();
            foreach ($this->tableNames as $this->tableName) {
                list($this->controllerName, $this->controllerClassName, $this->controllerClassFile) = 
                    ModuleCommand::getControllerNames($this->moduleCommand->getDispatcher(), $this->moduleName, $this->tableName);
                if (in_array($this->controllerName, $this->existsControllers)) {
                    print "[INFO ] controller {$this->controllerName} exists. skip." . PHP_EOL;
                    continue;
                }
                $this->createMaintController();
                $pks = S2Dao_DatabaseMetaDataUtil::getPrimaryKeySet($this->pdo,$this->tableName);
                if (count($pks) == 1) {
                    $this->executeScaffoldCommand();
                } else {
                    $this->executePagerCommand();
                }
                $controllers[] = $this->controllerName;
            }
            $this->createMaintIndexController($controllers);
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    private function executeScaffoldCommand() {
        $this->setupCommonProperty($this->scaffoldCommand);

        $pks = S2Dao_DatabaseMetaDataUtil::getPrimaryKeySet($this->pdo,$this->tableName);
        $this->scaffoldCommand->setPrimaryKey($pks[0]);
        $this->scaffoldCommand->setPrimaryProp(EntityCommand::getPropertyNameFromCol($pks[0]));
        $this->scaffoldCommand->setCols($this->dbms->getColumns($this->tableName));
        $this->scaffoldCommand->setExtendsEntityClassName('none');
        $this->scaffoldCommand->mergeEntityPropertyNamesFromCols();
        $this->scaffoldCommand->prepareFiles();
    }

    private function executePagerCommand() {
        $this->setupCommonProperty($this->pagerCommand);

        $this->pagerCommand->setCols($this->dbms->getColumns($this->tableName));
        $this->pagerCommand->setExtendsEntityClassName('none');
        $this->pagerCommand->mergeEntityPropertyNamesFromCols();
        $this->pagerCommand->prepareFiles();
    }
    
    private function setupCommonProperty($command) {
        $actionName = $this->controllerName;
        $command->setEntityPropertyNames(array());
        $command->setModuleName($this->moduleName);
        $command->setControllerName($this->controllerName);
        $command->setControllerClassName($this->controllerClassName);
        $command->setControllerClassFile($this->controllerClassFile);
        $command->setCtlServiceInterfaceName(ModuleCommand::getCtlServiceInterfaceName($this->controllerName));
        $command->setActionName($actionName);
        $command->setFormatActionName(
            $command->getDispatcher()->formatName($actionName));
        $command->setActionMethodName(
            $command->getDispatcher()->formatActionName($actionName));
        $command->setUseDao(true);
        $command->setUseCommonsDao(false);
        $command->setUseDB(true);
        $command->setupPropertyWithDao($actionName);
        $command->setEntityExtends(true);
        $command->setTableNames($this->tableNames);
        $command->setTableName($this->tableName);
    }

    private function createMaintController() {
        $this->moduleCommand->setModuleName($this->moduleName);
        $this->moduleCommand->setControllerName($this->controllerName);
        $this->moduleCommand->setControllerClassName($this->controllerClassName);
        $this->moduleCommand->setControllerClassFile($this->controllerClassFile);
        $this->moduleCommand->setCtlServiceInterfaceName(
            ModuleCommand::getCtlServiceInterfaceName($this->controllerName));
        $this->moduleCommand->createDirectory();
        $this->moduleCommand->prepareFiles();
    }

    private function createMaintIndexController($controllers) {
        $controllerName = 'mm';
        list($controllerName, $controllerClassName, $controllerClassFile) = 
            ModuleCommand::getControllerNames($this->moduleCommand->getDispatcher(), $this->moduleName, $controllerName);

        $ctlServiceInterfaceName = ModuleCommand::getCtlServiceInterfaceName($controllerName);
        $this->moduleCommand->setModuleName($this->moduleName);
        $this->moduleCommand->setControllerName($controllerName);
        $this->moduleCommand->setControllerClassName($controllerClassName);
        $this->moduleCommand->setControllerClassFile($controllerClassFile);
        $this->moduleCommand->setCtlServiceInterfaceName($ctlServiceInterfaceName);
        $this->moduleCommand->createDirectory();

        $this->moduleCommand->prepareModuleServiceInterfaceFile();
        $this->moduleCommand->prepareModuleIncFile();
        $this->prepareActionControllerClassFile($controllerName,
                                                $controllerClassName,
                                                $ctlServiceInterfaceName,
                                                $controllerClassFile,
                                                $controllers);
        $this->prepareIndexFile($controllerName);
    }

    protected function finalConfirm(){
        print PHP_EOL. '[ generate information ]'  . PHP_EOL;
        print "  module name : {$this->moduleName}" . PHP_EOL;
        print "  tables      : " . implode(', ', $this->tableNames) . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    public function prepareActionControllerClassFile($controllerName,
                                                     $controllerClassName,
                                                     $ctlServiceInterfaceName,
                                                     $controllerClassFile,
                                                     $controllers){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DS
                 . $controllerClassFile
                 . S2BASE_PHP5_CLASS_SUFFIX; 

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/master-maint/controller.php");
        $keys = array("/@@CONTROLLER_CLASS_NAME@@/",
                      "/@@SERVICE_CLASS_NAME@@/",
                      "/@@CONTROLLER_NAME@@/",
                      "/@@TEMPLATE_NAME@@/",
                      "/@@CONTROLLERS@@/");
        $reps = array($controllerClassName,
                      $ctlServiceInterfaceName,
                      $controllerName,
                      'index' . S2BASE_PHP5_ZF_TPL_SUFFIX,
                      "'" . implode("','", $controllers) . "'");
        $tempContent = preg_replace($keys, $reps, $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    public function prepareIndexFile($controllerName){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DS
                 . $controllerName
                 . S2BASE_PHP5_DS
                 . S2BASE_PHP5_VIEW_DIR
                 . 'index'
                 . S2BASE_PHP5_ZF_TPL_SUFFIX; 


        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/pager/html_header.php");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeleton/master-maint/html.php");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/pager/html_footer.php");
        }

        $keys = array("/@@MODULE_NAME@@/",
                      "/@@CONTROLLER_NAME@@/");
        $reps = array($this->moduleName,
                      $controllerName);
        $tempContent = preg_replace($keys, $reps, $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

}
?>
