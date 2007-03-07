<?php
class ScaffoldCommand extends AbstractGoyaCommand {
    const DTO_SUFFIX = 'Dto';
    const VALIDATE_DIR = '/validate/';
    protected $dtoClassName;
    protected $primaryKey;
    protected $primaryProp;

    public function getDtoClassName() {
        return $this->dtoClassName;
    }
    public function setDtoClassName($dtoClassName) {
        $this->dtoClassName = $dtoClassName;
    }

    public function getPrimaryKey() {
        return $this->primaryKey;
    }
    public function setPrimaryKey($primaryKey) {
        $this->primaryKey = $primaryKey;
    }

    public function getPrimaryProp() {
        return $this->primaryProp;
    }
    public function setPrimaryProp($primaryProp) {
        $this->primaryProp = $primaryProp;
    }
    
    public function getName(){
        return "goya scaffold";
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
        return false;
    }

    protected function isUseDao() {
        return true;
    }

    protected function getGoyaInfoWithDB($actionName) {
        $this->setupPropertyWithDao($actionName);

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
        $this->primaryProp = EntityCommand::getPropertyNameFromCol($this->primaryKey);
        $this->cols = $dbms->getColumns($this->tableName);
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

/*
    protected function mergeEntityPropertyNamesFromCols() {
        foreach ($this->cols as $col) {
            array_push($this->entityPropertyNames,
                       EntityCommand::getPropertyNameFromCol($col));
        }
        $this->entityPropertyNames = array_unique($this->entityPropertyNames);
    }
*/
    public function setupPropertyWithDao($actionName){
        parent::setupPropertyWithDao($actionName);
        $this->dtoClassName = ucfirst($this->formatActionName) . self::DTO_SUFFIX;
    }

    protected function finalConfirm(){
        print PHP_EOL. '[ generate information ]'  . PHP_EOL;
        print "  module name               : {$this->moduleName}" . PHP_EOL;
        print "  controller name           : {$this->controllerName}" . PHP_EOL;
        print "  action name               : {$this->actionName}" . PHP_EOL;
        print "  format action name        : {$this->formatActionName}" . PHP_EOL;
        print "  action method name        : {$this->actionMethodName}" . PHP_EOL;
        print "  action dicon file name    : {$this->actionMethodName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  action template file      : {$this->actionName}" . S2BASE_PHP5_ZF_TPL_SUFFIX . PHP_EOL;
        print "  service interface name    : {$this->serviceInterfaceName}" . PHP_EOL;
        print "  service class name        : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name   : {$this->serviceClassName}Test" . PHP_EOL;
        print "  service dicon file name   : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  condition dto class name  : {$this->dtoClassName}" . PHP_EOL;
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
        $this->srcModuleDir  = S2BASE_PHP5_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->srcCtlDir     = $this->srcModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . $this->moduleName . S2BASE_PHP5_DS;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . $this->controllerName . S2BASE_PHP5_DS;

        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareActionDiconFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDtoFile();
        $this->prepareValidatorFile();
        if ($this->useDao) {
            $this->prepareServiceDiconFile();
            $this->prepareServiceClassFile();
            $this->prepareDaoTestFile();
            if (!$this->useCommonsDao) {
                $this->prepareDaoFile();
                $this->prepareEntityFile();
            }
        }
    }

    /**
     * Action file setting
     */
    protected function prepareActionFile(){
        $this->prepareActionFileList();
        $this->prepareActionFileConfirm();
        $this->prepareActionFileByFunc('create');
        $this->prepareActionFileByFunc('update');
        $this->prepareActionFileByFunc('delete');
        $this->prepareActionFileByFunc('execute');
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

    protected function prepareActionFileByFunc($func){
        $actionMethodName = $this->dispatcher->formatActionName($this->actionName . '-' . $func);

        $srcFile = $this->srcModuleDir
                 . $this->controllerClassFile
                 . S2BASE_PHP5_CLASS_SUFFIX;

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/scaffold/action_$func.tpl");

        $patterns = array("/@@ACTION_METHOD_NAME@@/",
                          "/@@DTO_SESSION_KEY@@/",
                          "/@@ACTION_NAME@@/",
                          "/@@UNIQUE_KEY_NAME@@/",
                          "/@@ENTITY_CLASS_NAME@@/",
                          "/@@PRE_ACTION_METHOD_NAME@@/");
        $replacements = array($actionMethodName,
                              strtolower($this->dtoClassName),
                              $this->actionName,
                              $this->primaryProp,
                              $this->entityClassName,
                              'pre' . ucfirst($actionMethodName));
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        $this->insertActionMethod($srcFile,$tempContent);
    }

    protected function prepareActionFileConfirm(){
        $actionMethodName = $this->dispatcher->formatActionName($this->actionName . '-Confirm');

        $srcFile = $this->srcModuleDir
                 . $this->controllerClassFile
                 . S2BASE_PHP5_CLASS_SUFFIX;

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/action_confirm.tpl');
        $serviceProp = strtolower(substr($this->serviceInterfaceName,0,1)) . substr($this->serviceInterfaceName,1);
        $patterns = array("/@@ACTION_METHOD_NAME@@/",
                          "/@@ACTION_NAME@@/",
                          "/@@ENTITY_CLASS_NAME@@/",
                          "/@@CREATE_DTO_METHOD@@/",
                          "/@@DTO_SESSION_KEY@@/",
                          "/@@PRE_ACTION_METHOD_NAME@@/",
                          "/@@VALIDATOR_CLASS_NAME@@/");
        $replacements = array($actionMethodName,
                              $this->actionName,
                              $this->entityClassName,
                              $this->getCreateDtoMethodSrc(),
                              strtolower($this->dtoClassName),
                              'pre' . ucfirst($actionMethodName),
                              ucfirst($this->formatActionName) . 'ConfirmValidator');
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        $this->insertActionMethod($srcFile,$tempContent);
    }

    protected function getCreateDtoMethodSrc() {
        $src = PHP_EOL;
        foreach ($this->entityPropertyNames as $prop) {
            $src .= '        $dto->set' . ucfirst($prop) . '($request->getParam(\'' . $prop . "'));" . PHP_EOL;
        }
        return $src;
    }

    protected function prepareActionFileList(){
        $srcFile = $this->srcModuleDir
                 . $this->controllerClassFile
                 . S2BASE_PHP5_CLASS_SUFFIX;

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/action_list.tpl');
        $serviceProp = strtolower(substr($this->serviceInterfaceName,0,1)) . substr($this->serviceInterfaceName,1);
        $patterns = array("/@@ACTION_METHOD_NAME@@/",
                          "/@@ACTION_NAME@@/",
                          "/@@CONDITION_DTO_NAME@@/",
                          "/@@CONDITION_DTO_SESSION_KEY@@/");
        $replacements = array($this->actionMethodName,
                              $this->actionName,
                              $this->dtoClassName,
                              strtolower($this->dtoClassName . '_condition'));
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        $this->insertActionMethod($srcFile,$tempContent);
    }

    /**
     * Template file setting
     */
    protected function prepareHtmlFile(){
        $this->prepareHtmlFileList();
        $this->prepareHtmlFileInput();
        $this->prepareHtmlFileConfirm();
    }

    protected function prepareHtmlFileList(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . S2BASE_PHP5_ZF_TPL_SUFFIX; 

        $contents = $this->prepareHtmlFileHeader();

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/scaffold/html_list.tpl");
        $patterns = array("/@@PROPERTY_ROWS_TITLE@@/",
                          "/@@PROPERTY_ROWS@@/",
                          "/@@ACTION_NAME@@/");
        $replacements = array($this->getPropertyRowsTitle(),
                              $this->getPropertyRowsHtml(),
                              $this->actionName);
        $contents .= preg_replace($patterns,$replacements,$tempContent);

        $contents .= $this->prepareHtmlFileFooter();

        S2Base_CommandUtil::writeFile($srcFile,$contents);
    }

    protected function getPropertyRowsTitle() {
        $src = "<tr>";
        foreach ($this->entityPropertyNames as $prop) {
            $src .= "<th>";
            $src .= ucfirst($prop);
            $src .= "</th>";
        }
        return $src . '<th colspan="2"></th></tr>';
    }

    protected function getPropertyRowsHtml() {
        $src = "<tr>";
        foreach ($this->entityPropertyNames as $prop) {
            $src .= "<td>";
            $src .= '{$row->get' . ucfirst($prop) . '()|escape}';
            $src .= "</td>" . PHP_EOL;
        }
        $src .= "<td>";
        $src .= '<a href="{$ctl_url}/'
              . $this->actionName . '-update/'
              . $this->primaryProp . '/{$row->get' . ucfirst($this->primaryProp) . '()}'
              . '">update</a>';
        $src .= "</td>" . PHP_EOL;
        $src .= "<td>";
        $src .= '<a href="{$ctl_url}/'
              . $this->actionName . '-delete/'
              . $this->primaryProp . '/{$row->get' . ucfirst($this->primaryProp) . '()}'
              . '">delete</a>';
        $src .= "</td>" . PHP_EOL;
        return $src . "</tr>";
    }

    protected function prepareHtmlFileInput(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . '-input'
                 . S2BASE_PHP5_ZF_TPL_SUFFIX; 

        $contents = $this->prepareHtmlFileHeader();

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/scaffold/html_input.tpl");
        $patterns = array("/@@FORM@@/");
        $replacements = array($this->getInputHtmlForm());
        $contents .= preg_replace($patterns,$replacements,$tempContent);

        $contents .= $this->prepareHtmlFileFooter();

        S2Base_CommandUtil::writeFile($srcFile,$contents);
    }

    protected function prepareHtmlFileConfirm(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . '-confirm'
                 . S2BASE_PHP5_ZF_TPL_SUFFIX; 

        $contents = $this->prepareHtmlFileHeader();

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/scaffold/html_confirm.tpl");
        $patterns = array("/@@FORM@@/");
        $replacements = array($this->getConfirmHtmlForm());
        $contents .= preg_replace($patterns,$replacements,$tempContent);

        $contents .= $this->prepareHtmlFileFooter();

        S2Base_CommandUtil::writeFile($srcFile,$contents);

    }

    protected function getInputHtmlForm(){
        $src = '<form method="post" action="{$ctl_url}/' . $this->actionName . '-confirm"><table>' . PHP_EOL;
        $prop = $this->primaryProp;
        $src .= "<tr><td>";
        $src .= ucfirst($prop);
        $src .= '</td>' . PHP_EOL;
        $src .= '<td>' . PHP_EOL;
        $src .= '{if $func == \'create\'}' . PHP_EOL;
        $src .= '<input type="text" name="' . $prop . '" value="';
        $src .= '{$dto->get' . ucfirst($prop) . '()}"/>' . PHP_EOL;
        $src .= '</td>' . PHP_EOL;
        $src .= "<td>";
        $src .= '<font color="pink">{$errors.validate.' . $prop . '.msg|escape}</font>';
        $src .= "</td></tr>" . PHP_EOL;
        $src .= '{else}' . PHP_EOL;
        $src .= '{$dto->get' . ucfirst($prop) . '()|escape}' . PHP_EOL;
        $src .= '<input type="hidden" name="' . $prop . '" value="';
        $src .= '{$dto->get' . ucfirst($prop) . '()}"/>' . PHP_EOL;
        $src .= '</td>' . PHP_EOL;
        $src .= '{/if}' . PHP_EOL;

        foreach ($this->entityPropertyNames as $prop) {
            if ($prop == $this->primaryProp) {
                continue;
            }
            $src .= "<tr><td>";
            $src .= ucfirst($prop);
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '<input type="text" name="' . $prop . '" value="';
            $src .= '{$dto->get' . ucfirst($prop) . '()}"/>';
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '<font color="pink">{$errors.validate.' . $prop . '.msg|escape}</font>';
            $src .= "</td></tr>" . PHP_EOL;
        }
        $src .= "</table>" . PHP_EOL;
        $src .= '<input type="hidden" name="func" value="{$func}"/>' . PHP_EOL;
        $src .= '<input type="submit"/>' . PHP_EOL;
        return $src . "</form>" . PHP_EOL;
    }

    protected function getConfirmHtmlForm(){
        $src = '<form method="post" action="{$ctl_url}/' . $this->actionName . '-execute"><table>' . PHP_EOL;
        foreach ($this->entityPropertyNames as $prop) {
            $src .= "<tr><td>";
            $src .= ucfirst($prop);
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '{$dto->get' . ucfirst($prop) . '()|escape}';
            $src .= "</td>" . PHP_EOL;
        }
        $src .= "</table>" . PHP_EOL;
        $src .= '<input type="hidden" name="func" value="{$func}"/>' . PHP_EOL;
        $src .= '<input type="submit"/>' . PHP_EOL;
        return $src . "</form>" . PHP_EOL;
    }

    protected function prepareHtmlFileHeader(){
        $contents = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/scaffold/html_header.tpl");
            $patterns = array("/@@MODULE_NAME@@/",
                              "/@@CONTROLLER_NAME@@/",
                              "/@@ACTION_NAME@@/");
            $replacements = array($this->moduleName,
                                  $this->controllerName,
                                  $this->actionName);
            $contents = preg_replace($patterns,$replacements,$tempContent);
        }
        return $contents;
    }

    protected function prepareHtmlFileFooter(){
        $contents = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $contents = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeleton/scaffold/html_footer.tpl");
        }
        return $contents;
    }

    /**
     * Service file setting
     */
    protected function prepareServiceClassFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/service.tpl');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);
        if ($this->serviceInterfaceName == $this->ctlServiceInterfaceName) {
            $implementsInterface = $this->serviceInterfaceName;
        } else {
            $implementsInterface = $this->serviceInterfaceName . ', ' . $this->ctlServiceInterfaceName;
        }
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@INTERFACE_NAME@@/",
                          "/@@DAO_NAME@@/",
                          "/@@DAO_PROPERTY@@/",
                          "/@@CONDITION_DTO_NAME@@/",
                          "/@@ENTITY_CLASS_NAME@@/");
        $replacements = array($this->serviceClassName,
                              $implementsInterface,
                              $this->daoInterfaceName,
                              $daoProp,
                              $this->dtoClassName,
                              $this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceInterfaceFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/service_interface.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@ENTITY_CLASS_NAME@@/");
        $replacements = array($this->serviceInterfaceName,
                              $this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    /**
     * Dao file setting
     */
    protected function prepareDaoFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/dao.tpl');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@ENTITY_NAME@@/",
                          "/@@CONDITION_DTO_NAME@@/",
                          "/@@UNIQUE_KEY_NAME@@/");
        $replacements = array($this->daoInterfaceName,
                              $this->entityClassName,
                              $this->dtoClassName,
                              $this->primaryProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    /**
     * Dicon file setting
     */
    protected function prepareActionDiconFile(){
        parent::prepareActionDiconFile();
        $this->prepareActionDiconFileByFunc('execute');
        $this->prepareActionDiconFileByFunc('delete');
        $this->prepareActionDiconFileByFunc('update');
        $this->prepareActionDiconFileByFunc('confirm');
    }

    protected function prepareActionDiconFileByFunc($func){
        $actionMethodName = $this->dispatcher->formatActionName($this->actionName . '-' . $func);

        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DICON_DIR
                 . $actionMethodName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeleton/scaffold/action_dicon_$func.tpl");
        $patterns = array("/@@CONTROLLER_CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@CONTROLLER_NAME@@/",
                          "/@@SERVICE_CLASS@@/",
                          "/@@VALIDATOR_CLASS_NAME@@/");
        $replacements = array($this->controllerClassName,
                              $this->moduleName,
                              $this->controllerName,
                              $this->serviceClassName,
                              ucfirst($this->formatActionName) . 'ConfirmValidator');
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceDiconFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/pager/service_dicon.tpl');

        $patterns = array("/@@SERVICE_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array($this->serviceClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    /**
     * Dto file setting
     */
    protected function prepareDtoFile(){
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_ENTITY_DIR
                 . $this->dtoClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/dto.tpl');
        $patterns = array("/@@CONDITION_DTO_NAME@@/");
        $replacements = array($this->dtoClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    /**
     * Validator file setting
     */
    protected function prepareValidatorFile(){
        $validatorDir = $this->srcCtlDir . self::VALIDATE_DIR;
        S2Base_CommandUtil::createDirectory($validatorDir);
        $this->prepareValidatorFileRegexp();
        $this->prepareValidatorFileConfirm();
        $this->prepareValidatorIniFileByFunc('update');
        $this->prepareValidatorIniFileByFunc('delete');
        $this->prepareValidatorIniFileConfirm();
        $this->prepareValidatorIniFileExecute();
    }

    protected function prepareValidatorIniFileConfirm() {
        //$actionMethodName = $this->dispatcher->formatActionName($this->actionName . '-confirm');
        $srcFile = $this->srcCtlDir
                 . self::VALIDATE_DIR
                 . $this->actionName . '-confirm'
                 . '.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/validate_confirm_ini.tpl');
        $patterns = array("/@@RETURN_ACTION@@/",
                          "/@@PARAMS@@/",
                          "/@@ACTION_NAME@@/");
        $replacements = array($this->actionName. '-create',
                              $this->getConfirmValidateInfo(),
                              ucfirst($this->formatActionName));
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function getConfirmValidateInfo() {
        $ret = '';
        foreach ($this->entityPropertyNames as $prop) {
            $ret .= "[$prop : default]" . PHP_EOL;
            $ret .= 'validate = "regex"' . PHP_EOL;
            if ($prop == $this->primaryProp) {
                $ret .= 'regex.pattern = "/^.{1,8}$/"' . PHP_EOL;
            } else {
                $ret .= 'regex.pattern = "/^.{0,8}$/"' . PHP_EOL;
            }
            $ret .= 'regex.msg    = "invalid value"' . PHP_EOL . PHP_EOL;
        }
        return $ret;
    }

    protected function prepareValidatorIniFileExecute() {
        //$actionMethodName = $this->dispatcher->formatActionName($this->actionName . '-execute');

        $srcFile = $this->srcCtlDir
                 . self::VALIDATE_DIR
                 . $this->actionName . '-execute'
                 . '.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/validate_execute_ini.tpl');
        $patterns = array("/@@ACTION_NAME@@/");
        $replacements = array($this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareValidatorIniFileByFunc($func) {
        //$actionMethodName = $this->dispatcher->formatActionName($this->actionName . '-' . $func);

        $srcFile = $this->srcCtlDir
                 . self::VALIDATE_DIR
                 . $this->actionName . '-' . $func
                 . '.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/validate_ini.tpl');
        $patterns = array("/@@PARAM_KEY@@/",
                          "/@@RETURN_ACTION_NAME@@/");
        $replacements = array($this->primaryProp,
                              $this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareValidatorFileConfirm() {
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_INTERCEPTOR_DIR
                 . ucfirst($this->formatActionName) . 'ConfirmValidator'
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/validate_confirm.tpl');
        $patterns = array("/@@ACTION_NAME@@/",
                          "/@@DTO_CLASS_NAME@@/",
                          "/@@CONTROLLER_CLASS_NAME@@/",
                          "/@@ENTITY_CLASS_NAME@@/");
        $replacements = array(ucfirst($this->formatActionName),
                              $this->dtoClassName,
                              $this->controllerClassName,
                              $this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareValidatorFileRegexp() {
        $srcFile = $this->srcCtlDir
                 . S2BASE_PHP5_INTERCEPTOR_DIR
                 . 'RegexpValidator'
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/scaffold/validate_regexp.tpl');
        $patterns = array();
        $replacements = array();
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
