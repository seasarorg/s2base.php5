<?php
class ModelScaffoldCommand extends ModelPagerCommand {

    public function __construct(){
        parent::__construct();
    }

    /**
     * @see S2Base_GenerateCommand::getName()
     */
    public function getName(){
        return "model scaffold";
    }

    /**
     * @see S2Base_GenerateCommand::isAvailable()
     */
    public function isAvailable(){
        return true;
    }

    protected function isUseDB() {
        return true;
    }

    protected function prepareFiles(){
        $this->appModuleDir  = S2BASE_PHP5_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->appCtlDir     = $this->appModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;
        $this->appViewDir    = $this->appModuleDir . S2BASE_PHP5_DS . 'views';
        $this->testModuleDir = S2BASE_PHP5_TEST_MODULES_DIR . S2BASE_PHP5_DS . $this->moduleName;
        $this->testCtlDir    = $this->testModuleDir . S2BASE_PHP5_DS . 'models' . S2BASE_PHP5_DS . $this->controllerName;

        $this->prepareActionFile();
        $this->prepareModelClassFile();
        $this->prepareModelTestFile();
        $this->prepareConditionDtoFile();
        $this->prepareValidateFile();
        $this->prepareHtmlFile();
    }

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
                     . "/skeletons/model-scaffold/action_$func.tpl");
        $modelProp = strtolower(substr($this->modelClassName,0,1)) . substr($this->modelClassName,1);

        $patterns = array("/@@ACTION_METHOD_NAME@@/",
                          "/@@DTO_SESSION_KEY@@/",
                          "/@@ACTION_NAME@@/",
                          "/@@UNIQUE_KEY_NAME@@/",
                          "/@@MODEL_CLASS@@/",
                          "/@@MODEL_PROPERTY@@/");
        $replacements = array($actionMethodName,
                              strtolower($this->dtoClassName),
                              $this->actionName,
                              $this->primaryKey,
                              $this->modelClassName,
                              $modelProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        ActionCommand::insertActionMethod($srcFile,$tempContent);
    }

    protected function prepareActionFileConfirm(){
        $actionMethodName = $this->dispatcher->formatActionName($this->actionName . '-Confirm');

        $srcFile = $this->appModuleDir
                 . S2BASE_PHP5_DS . 'controllers'
                 . S2BASE_PHP5_DS . $this->controllerClassFile . S2BASE_PHP5_CLASS_SUFFIX;

        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeletons/model-scaffold/action_confirm.tpl');
        $modelProp = strtolower(substr($this->modelClassName,0,1)) . substr($this->modelClassName,1);
        $patterns = array("/@@ACTION_METHOD_NAME@@/",
                          "/@@ACTION_NAME@@/",
                          "/@@MODEL_CLASS@@/",
                          "/@@CREATE_DTO_METHOD@@/",
                          "/@@MODEL_PROPERTY@@/");
        $replacements = array($actionMethodName,
                              $this->actionName,
                              $this->modelClassName,
                              $this->getCreateDtoMethodSrc(),
                              $modelProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        ActionCommand::insertActionMethod($srcFile,$tempContent);
    }

    protected function getCreateDtoMethodSrc() {
        $src = PHP_EOL;
        foreach ($this->cols as $prop) {
            $src .= '        $row->' . $prop . ' = $request->getParam(\'' . $prop . "');" . PHP_EOL;
        }
        return $src;
    }

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
        foreach ($this->cols as $prop) {
            $src .= "<th>";
            $src .= $prop;
            $src .= "</th>";
        }
        return $src . '<th colspan="2"></th></tr>';
    }

    protected function getPropertyRowsHtml($isStdView = false) {
        $src = "<tr>";
        foreach ($this->cols as $prop) {
            $src .= "<td>";
            if ($isStdView) {
                $src .= '<?php echo $this->escape($row->' . $prop .');?>';
            } else {
                $src .= '{$row->' . $prop . '|escape}';
            }
            $src .= "</td>" . PHP_EOL;
        }
        $src .= "<td>";
        $src .= '<a href="';
        if ($isStdView) {
            $src .= '<?php echo "{$this->ctl_url}/' . $this->actionName . '-update/';
            $src .= $this->primaryKey . '/{$this->escape($row->' . $this->primaryKey . ')}";?>';
        } else {
            $src .= '{$ctl_url}/' . $this->actionName . '-update/';
            $src .= $this->primaryKey . '/{$row->' . $this->primaryKey . '|escape}';
        }
        $src .= '">update</a>';
        $src .= "</td>" . PHP_EOL;
        $src .= "<td>";
        $src .= '<a href="';
        if ($isStdView) {
            $src .= '<?php echo "{$this->ctl_url}/' . $this->actionName . '-delete/';
            $src .= $this->primaryKey . '/{$this->escape($row->' . $this->primaryKey . ')}";?>';
        } else {
            $src .= '{$ctl_url}/' . $this->actionName . '-delete/';
            $src .= $this->primaryKey . '/{$row->' . $this->primaryKey . '|escape}';
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
        $prop = $this->primaryKey;
        $src .= "<tr><td>";
        $src .= $prop;
        $src .= '</td>' . PHP_EOL;
        $src .= '<td>' . PHP_EOL;
        $src .= '<?php if ($this->func == \'create\'): ?>' . PHP_EOL;
        $src .= '<input type="text" name="' . $prop . '" value="';
        $src .= '<?php echo $this->dto->' . $prop . ';?>"/>' . PHP_EOL;
        $src .= '</td>' . PHP_EOL;
        $src .= "<td>";
        $src .= '<font color="pink"><?php if (isset($this->errors[\'validate\'][\'' . $prop . '\'])){echo $this->escape($this->errors[\'validate\'][\'' . $prop . '\'][\'msg\']);} ?></font>';
        $src .= "</td></tr>" . PHP_EOL;
        $src .= '<?php else: ?>' . PHP_EOL;
        $src .= '<?php echo $this->escape($this->dto->' . $prop . '); ?>' . PHP_EOL;
        $src .= '<input type="hidden" name="' . $prop . '" value="';
        $src .= '<?php echo $this->dto->' . $prop . ';?>"/>' . PHP_EOL;
        $src .= '</td>' . PHP_EOL;
        $src .= '<?php endif;?>' . PHP_EOL;

        foreach ($this->cols as $prop) {
            if ($prop == $this->primaryKey) {
                continue;
            }
            $src .= "<tr><td>";
            $src .= $prop;
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '<input type="text" name="' . $prop . '" value="';
            $src .= '<?php echo $this->dto->' . $prop . ';?>"/>';
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
        $prop = $this->primaryKey;
        $src .= "<tr><td>";
        $src .= $prop;
        $src .= '</td>' . PHP_EOL;
        $src .= '<td>' . PHP_EOL;
        $src .= '{if $func == \'create\'}' . PHP_EOL;
        $src .= '<input type="text" name="' . $prop . '" value="';
        $src .= '{$dto->' . $prop . '}"/>' . PHP_EOL;
        $src .= '</td>' . PHP_EOL;
        $src .= "<td>";
        $src .= '<font color="pink">{$errors.validate.' . $prop . '.msg|escape}</font>';
        $src .= "</td></tr>" . PHP_EOL;
        $src .= '{else}' . PHP_EOL;
        $src .= '{$dto->' . $prop . '|escape}' . PHP_EOL;
        $src .= '<input type="hidden" name="' . $prop . '" value="';
        $src .= '{$dto->' . $prop . '}"/>' . PHP_EOL;
        $src .= '</td>' . PHP_EOL;
        $src .= '{/if}' . PHP_EOL;

        foreach ($this->cols as $prop) {
            if ($prop == $this->primaryKey) {
                continue;
            }
            $src .= "<tr><td>";
            $src .= $prop;
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '<input type="text" name="' . $prop . '" value="';
            $src .= '{$dto->' . $prop . '}"/>';
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
        foreach ($this->cols as $prop) {
            $src .= "<tr><td>";
            $src .= $prop;
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '<?php echo $this->escape($this->dto->' . $prop . ');?>';
            $src .= "</td>" . PHP_EOL;
        }
        $src .= "</table>" . PHP_EOL;
        $src .= '<input type="hidden" name="func" value="<?php echo $this->func;?>"/>' . PHP_EOL;
        $src .= '<input type="submit"/>' . PHP_EOL;
        return $src . "</form>" . PHP_EOL;
    }

    protected function getConfirmHtmlForm(){
        $src = '<form method="post" action="{$ctl_url}/' . $this->actionName . '-execute"><table>' . PHP_EOL;
        foreach ($this->cols as $prop) {
            $src .= "<tr><td>";
            $src .= $prop;
            $src .= "</td>" . PHP_EOL;

            $src .= "<td>";
            $src .= '{$dto->' . $prop . '|escape}';
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

    protected function prepareValidateFile(){
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
        foreach ($this->cols as $prop) {
            $ret .= "[$prop : default]" . PHP_EOL;
            $ret .= 'validate = "regex"' . PHP_EOL;
            if ($prop == $this->primaryKey) {
                $ret .= 'regex.pattern = "/^\d{1,8}$/"' . PHP_EOL;
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
        $replacements = array($this->primaryKey,
                              $this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
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
                     . '/skeletons/model-scaffold/model.tpl');

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
