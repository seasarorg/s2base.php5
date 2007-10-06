<?php
class ScaffoldCommand extends PagerCommand {
    const DTO_SUFFIX = 'Dto';
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

    public function isAvailable(){
        return true;
    }

    public function execute(){
        parent::execute();
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
        $this->daoInterfaceName = ucfirst(EntityCommand::getPropertyNameFromCol($this->tableName)) . S2DaoSkelConst::DaoName;
        $this->entityClassName  = ucfirst(EntityCommand::getPropertyNameFromCol($this->tableName)) . S2DaoSkelConst::BeanName;
        $this->extendsEntityClassName = "none";

        $daoInterfaceNameTmp = S2Base_StdinManager::getValue("dao interface name ? [{$this->daoInterfaceName}] : ");
        $this->daoInterfaceName = trim($daoInterfaceNameTmp) == '' ? $this->daoInterfaceName : $daoInterfaceNameTmp;
        $this->validate($this->daoInterfaceName);

        $entityClassNameTmp = S2Base_StdinManager::getValue("entity class name ? [{$this->entityClassName}] : ");
        $this->entityClassName = trim($entityClassNameTmp) == '' ? $this->entityClassName : $entityClassNameTmp;
        $this->validate($this->entityClassName);

        $this->mergeEntityPropertyNamesFromCols();

        return true;
    }

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
        print "  action template file      : {$this->actionName}" . '.' . S2BASE_PHP5_ZF_TPL_SUFFIX . PHP_EOL;
        print "  service class name        : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name   : {$this->serviceClassName}Test" . PHP_EOL;
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
        $this->appModuleDir  = S2BASE_PHP5_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->appCtlDir     = $this->appModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;
        $this->appViewDir    = $this->appModuleDir . S2BASE_PHP5_DS . 'views';
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;

        $this->prepareActionFile();
        $this->prepareHtmlFile();
        $this->prepareServiceTestFile();
        $this->prepareConditionDtoFile();
        $this->prepareValidatorFile();
        if ($this->useDao) {
            $this->prepareServiceClassFile();
            $this->prepareDaoTestFile();
            if (!$this->useCommonsDao) {
                $this->prepareDaoFile();
                $this->prepareDaoSqlFile();
                $this->prepareEntityFile();
            }
        }
    }

    /**
     * Action file setting
     */
    protected function prepareActionFile(){
        parent::prepareActionFile();
        $this->prepareActionFileConfirm();
        $this->prepareActionFileByFunc('create');
        $this->prepareActionFileByFunc('update');
        $this->prepareActionFileByFunc('delete');
        $this->prepareActionFileByFunc('execute');
    }

    protected function prepareActionFileByFunc($func){
        $actionMethodName = $this->dispatcher->formatActionName($this->actionName . '-' . $func);

        $srcFile = $this->appModuleDir
                 . S2BASE_PHP5_DS . 'controllers'
                 . S2BASE_PHP5_DS . $this->controllerClassFile . S2BASE_PHP5_CLASS_SUFFIX;

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeletons/scaffold/action_$func.tpl");
        $serviceProp = strtolower(substr($this->serviceClassName,0,1)) . substr($this->serviceClassName,1);

        $patterns = array("/@@ACTION_METHOD_NAME@@/",
                          "/@@DTO_SESSION_KEY@@/",
                          "/@@ACTION_NAME@@/",
                          "/@@UNIQUE_KEY_NAME@@/",
                          "/@@ENTITY_CLASS_NAME@@/",
                          "/@@PRE_ACTION_METHOD_NAME@@/",
                          "/@@SERVICE_PROPERTY@@/");
        $replacements = array($actionMethodName,
                              strtolower($this->dtoClassName),
                              $this->actionName,
                              $this->primaryProp,
                              $this->entityClassName,
                              'pre' . ucfirst($actionMethodName),
                              $serviceProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        ActionCommand::insertActionMethod($srcFile,$tempContent);
    }

    protected function prepareActionFileConfirm(){
        $actionMethodName = $this->dispatcher->formatActionName($this->actionName . '-Confirm');

        $srcFile = $this->appModuleDir
                 . S2BASE_PHP5_DS . 'controllers'
                 . S2BASE_PHP5_DS . $this->controllerClassFile . S2BASE_PHP5_CLASS_SUFFIX;

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/scaffold/action_confirm.tpl');
        $serviceProp = strtolower(substr($this->serviceClassName,0,1)) . substr($this->serviceClassName,1);
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
        ActionCommand::insertActionMethod($srcFile,$tempContent);
    }

    protected function getCreateDtoMethodSrc() {
        $src = PHP_EOL;
        foreach ($this->entityPropertyNames as $prop) {
            $src .= '        $dto->set' . ucfirst($prop) . '($request->getParam(\'' . $prop . "'));" . PHP_EOL;
        }
        return $src;
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
        $contents = $this->prepareHtmlFileHeader();

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeletons/scaffold/html_list$viewSuffix.tpl");
        $patterns = array("/@@PROPERTY_ROWS_TITLE@@/",
                          "/@@PROPERTY_ROWS@@/",
                          "/@@ACTION_NAME@@/");
        $replacements = array($this->getPropertyRowsTitle(),
                              $this->getPropertyRowsHtml(ModuleCommand::isStandardView()),
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

    protected function getPropertyRowsHtml($isStdView = false) {
        $src = "<tr>";
        foreach ($this->entityPropertyNames as $prop) {
            $src .= "<td>";
            if ($isStdView) {
                $src .= '<?php echo $this->escape($row->get' . ucfirst($prop) .'());?>';
            } else {
                $src .= '{$row->get' . ucfirst($prop) . '()|escape}';
            }
            $src .= "</td>" . PHP_EOL;
        }
        $src .= "<td>";
        $src .= '<a href="';
        if ($isStdView) {
            $src .= '<?php echo "{$this->ctl_url}/' . $this->actionName . '-update/';
            $src .= $this->primaryProp . '/{$this->escape($row->get' . ucfirst($this->primaryProp) . '())}";?>';
        } else {
            $src .= '{$ctl_url}/' . $this->actionName . '-update/';
            $src .= $this->primaryProp . '/{$row->get' . ucfirst($this->primaryProp) . '()|escape}';
        }
        $src .= '">update</a>';
        $src .= "</td>" . PHP_EOL;
        $src .= "<td>";
        $src .= '<a href="';
        if ($isStdView) {
            $src .= '<?php echo "{$this->ctl_url}/' . $this->actionName . '-delete/';
            $src .= $this->primaryProp . '/{$this->escape($row->get' . ucfirst($this->primaryProp) . '())}";?>';
        } else {
            $src .= '{$ctl_url}/' . $this->actionName . '-delete/';
            $src .= $this->primaryProp . '/{$row->get' . ucfirst($this->primaryProp) . '()|escape}';
        }
        $src .= '">delete</a>';
        $src .= "</td>" . PHP_EOL;
        return $src . "</tr>";
    }

    protected function prepareHtmlFileInput(){
        if (ModuleCommand::isStandardView()) {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . 'scripts'
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . $this->actionName . '-input.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        } else {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . $this->actionName . '-input.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        }
        $viewSuffix = ModuleCommand::getViewSuffixName();
        $contents = $this->prepareHtmlFileHeader();

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeletons/scaffold/html_input$viewSuffix.tpl");
        $patterns = array("/@@FORM@@/");

        if (ModuleCommand::isStandardView()) {
            $replacements = array($this->getInputHtmlFormStd());
        } else {
            $replacements = array($this->getInputHtmlForm());
        }
        $contents .= preg_replace($patterns,$replacements,$tempContent);

        $contents .= $this->prepareHtmlFileFooter();

        S2Base_CommandUtil::writeFile($srcFile,$contents);
    }

    protected function prepareHtmlFileConfirm(){
        if (ModuleCommand::isStandardView()) {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . 'scripts'
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . $this->actionName . '-confirm.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        } else {
            $srcFile = $this->appViewDir
                     . S2BASE_PHP5_DS . $this->controllerName
                     . S2BASE_PHP5_DS . $this->actionName . '-confirm.' . S2BASE_PHP5_ZF_TPL_SUFFIX; 
        }
        $contents = $this->prepareHtmlFileHeader();
        $viewSuffix = ModuleCommand::getViewSuffixName();
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeletons/scaffold/html_confirm$viewSuffix.tpl");
        $patterns = array("/@@FORM@@/");

        if (ModuleCommand::isStandardView()) {
            $replacements = array($this->getConfirmHtmlFormStd());
        } else {
            $replacements = array($this->getConfirmHtmlForm());
        }
        $contents .= preg_replace($patterns,$replacements,$tempContent);

        $contents .= $this->prepareHtmlFileFooter();

        S2Base_CommandUtil::writeFile($srcFile,$contents);
    }

    protected function getInputHtmlFormStd(){
        $src = '<form method="post" action="<?php echo "{$this->ctl_url}/' . $this->actionName . '-confirm"; ?>"><table>' . PHP_EOL;
        $prop = $this->primaryProp;
        $src .= "<tr><td>";
        $src .= ucfirst($prop);
        $src .= '</td>' . PHP_EOL;
        $src .= '<td>' . PHP_EOL;
        $src .= '<?php if ($this->func == \'create\'): ?>' . PHP_EOL;
        $src .= '<input type="text" name="' . $prop . '" value="';
        $src .= '<?php echo $this->dto->get' . ucfirst($prop) . '();?>"/>' . PHP_EOL;
        $src .= '</td>' . PHP_EOL;
        $src .= "<td>";
        $src .= '<font color="pink"><?php if (isset($this->errors[\'validate\'][\'' . $prop . '\'])){echo $this->escape($this->errors[\'validate\'][\'' . $prop . '\'][\'msg\']);} ?></font>';
        $src .= "</td></tr>" . PHP_EOL;
        $src .= '<?php else: ?>' . PHP_EOL;
        $src .= '<?php echo $this->escape($this->dto->get' . ucfirst($prop) . '()); ?>' . PHP_EOL;
        $src .= '<input type="hidden" name="' . $prop . '" value="';
        $src .= '<?php echo $this->dto->get' . ucfirst($prop) . '();?>"/>' . PHP_EOL;
        $src .= '</td>' . PHP_EOL;
        $src .= '<?php endif;?>' . PHP_EOL;

        foreach ($this->entityPropertyNames as $prop) {
            if ($prop == $this->primaryProp) {
                continue;
            }
            $src .= "<tr><td>";
            $src .= ucfirst($prop);
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '<input type="text" name="' . $prop . '" value="';
            $src .= '<?php echo $this->dto->get' . ucfirst($prop) . '();?>"/>';
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '<font color="pink"><?php if (isset($this->errors[\'validate\'][\'' . $prop . '\'])){echo $this->escape($this->errors[\'validate\'][\'' . $prop . '\'][\'msg\']);}?></font>';
            $src .= "</td></tr>" . PHP_EOL;
        }
        $src .= "</table>" . PHP_EOL;
        $src .= '<input type="hidden" name="func" value="<?php echo $this->func;?>"/>' . PHP_EOL;
        $src .= '<input type="submit"/>' . PHP_EOL;
        return $src . "</form>" . PHP_EOL;
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

    protected function getConfirmHtmlFormStd(){
        $src = '<form method="post" action="<?php echo "{$this->ctl_url}/' . $this->actionName . '-execute";?>"><table>' . PHP_EOL;
        foreach ($this->entityPropertyNames as $prop) {
            $src .= "<tr><td>";
            $src .= ucfirst($prop);
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '<?php echo $this->escape($this->dto->get' . ucfirst($prop) . '());?>';
            $src .= "</td>" . PHP_EOL;
        }
        $src .= "</table>" . PHP_EOL;
        $src .= '<input type="hidden" name="func" value="<?php echo $this->func;?>"/>' . PHP_EOL;
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
        $viewSuffix = ModuleCommand::getViewSuffixName();
        $contents = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . "/skeletons/module/html_header$viewSuffix.tpl");
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
                      . "/skeletons/module/html_footer.tpl");
        }
        return $contents;
    }

    /**
     * Service file setting
     */
    protected function prepareServiceClassFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_SERVICE_DIR
                 . S2BASE_PHP5_DS . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/scaffold/service.tpl');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@DAO_NAME@@/",
                          "/@@DAO_PROPERTY@@/",
                          "/@@CONDITION_DTO_NAME@@/",
                          "/@@ENTITY_CLASS_NAME@@/");
        $replacements = array($this->serviceClassName,
                              $this->daoInterfaceName,
                              $daoProp,
                              $this->dtoClassName,
                              $this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    /**
     * Dao file setting
     */
    protected function prepareDaoFile(){
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_DAO_DIR
                 . S2BASE_PHP5_DS . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/scaffold/dao.tpl');

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
     * Validator file setting
     */
    protected function prepareValidatorFile(){
        $this->prepareValidateIniFile();
        $this->prepareValidatorIniFileByFunc('update');
        $this->prepareValidatorIniFileByFunc('delete');
        $this->prepareValidatorIniFileConfirm();
        $this->prepareValidatorIniFileExecute();
    }

    protected function prepareValidatorIniFileConfirm() {
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_DS . ModuleCommand::VALIDATE_DIR
                 . S2BASE_PHP5_DS . $this->actionName . '-confirm'
                 . '.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/action/validate.ini.tpl');
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . '/skeletons/scaffold/validate_confirm_ini.tpl');
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
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_DS . ModuleCommand::VALIDATE_DIR
                 . S2BASE_PHP5_DS . $this->actionName . '-execute.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/action/validate.ini.tpl');
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . '/skeletons/scaffold/validate_execute_ini.tpl');
        $patterns = array("/@@ACTION_NAME@@/");
        $replacements = array($this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareValidatorIniFileByFunc($func) {
        $srcFile = $this->appCtlDir
                 . S2BASE_PHP5_DS . ModuleCommand::VALIDATE_DIR
                 . S2BASE_PHP5_DS . $this->actionName . '-' . $func
                 . '.ini';
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/action/validate.ini.tpl');
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . '/skeletons/scaffold/validate_ini.tpl');
        $patterns = array("/@@PARAM_KEY@@/",
                          "/@@RETURN_ACTION_NAME@@/");
        $replacements = array($this->primaryProp,
                              $this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
