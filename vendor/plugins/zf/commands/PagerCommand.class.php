<?php
class PagerCommand extends AbstractGoyaCommand {
    const CONDITION_DTO_SUFFIX = 'ConditionDto';
    private $conditionDtoClassName;
    private $conditionDtoSessionKey;
    private $entityPropertyNames;

    public function getName(){
        return "goya pager";
    }

    public function execute(){
        $this->entityPropertyNames = array();
        parent::execute();
    }

    protected function isUseCommonsDao() {
        return DaoCommand::isCommonsDaoAvailable();
    }

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function isEntityExtends() {
        return EntityCommand::isCommonsEntityAvailable();
    }

    protected function isUseDao() {
        return S2Base_StdinManager::isYes('use dao ?');
    }

    protected function getGoyaInfoWithCommonsDao($actionName){
        if(parent::getGoyaInfoWithCommonsDao($actionName)) {
            $this->conditionDtoClassName = ucfirst($actionName) . self::CONDITION_DTO_SUFFIX;
            $this->conditionDtoSessionKey = $actionName . self::CONDITION_DTO_SUFFIX;
            $this->entityPropertyNames = $this->getEntityPropertyNames($this->entityClassName);
            return true;
        }
        return false;
    }

    protected function getGoyaInfoWithDB($actionName) {
        if(parent::getGoyaInfoWithDB($actionName)) {
            $this->mergeEntityPropertyNamesFromCols();
            return true;
        }
        return false;
    }

    protected function getGoyaInfoInteractive($actionName) {
        if(parent::getGoyaInfoInteractive($actionName)) {
            if ($this->entityExtends) {
                $this->entityPropertyNames = $this->getEntityPropertyNames($this->extendsEntityClassName);
            }
            $this->mergeEntityPropertyNamesFromCols();
            return true;
        }
        return false;
    }

    protected function setupPropertyWithDao($actionName){
        parent::setupPropertyWithDao($actionName);
        $this->conditionDtoClassName = ucfirst($actionName) . self::CONDITION_DTO_SUFFIX;
        $this->conditionDtoSessionKey = $actionName . self::CONDITION_DTO_SUFFIX;
    }

    protected function setupPropertyWithoutDao($actionName){
        parent::setupPropertyWithoutDao($actionName);
        $this->conditionDtoClassName = ucfirst($actionName) . self::CONDITION_DTO_SUFFIX;
        $this->conditionDtoSessionKey = $actionName . self::CONDITION_DTO_SUFFIX;
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name               : {$this->moduleName}" . PHP_EOL;
        print "  action name               : {$this->actionName} " . PHP_EOL;

        print "  action method name        : {$this->actionMethodName}" . PHP_EOL;
        print "  action dicon file name    : {$this->actionClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  action template file      : {$this->actionName}" . S2BASE_PHP5_ZF_TPL_SUFFIX . PHP_EOL;
        print "  service interface name    : {$this->serviceInterfaceName}" . PHP_EOL;
        print "  service class name        : {$this->serviceClassName}" . PHP_EOL;
        print "  service test class name   : {$this->serviceClassName}Test" . PHP_EOL;
        print "  service dicon file name   : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        print "  condition dto class name  : {$this->conditionDtoClassName}" . PHP_EOL;
        print "  condition dto session key : {$this->conditionDtoSessionKey}" . PHP_EOL;
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

    protected function prepareFiles(){
        $this->prepareActionFile();
        $this->prepareActionDiconFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareConditionDtoFile();
        if ($this->useDao) {
            $this->prepareServiceDiconFile();
            $this->prepareServiceClassFile();
            $this->prepareDaoTestFile();
            $this->prepareHtmlFile();
            if (!$this->useCommonsDao) {
                $this->prepareDaoFile();
                $this->prepareEntityFile();
            }
        } else {
            $this->prepareHtmlFileWithoutDao();
            $this->prepareServiceClassFileWithoutDao();
            $this->prepareServiceDiconFileWithoutDao();
        }
        if ($this->useCommonsDao) {
            $this->showMethodDefinitionMessage();
        }
    }

    protected function prepareActionFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->controllerClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempAction = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                    . '/skeleton/pager/action.php');

        $patterns = array("/@@ACTION_NAME@@/",
                          "/@@TEMPLATE_NAME@@/",
                          "/@@CONDITION_DTO_NAME@@/",
                          "/@@CONDITION_DTO_SESSION_KEY@@/");
        $replacements = array($this->actionMethodName,
                              $this->actionName . S2BASE_PHP5_ZF_TPL_SUFFIX,
                              $this->conditionDtoClassName,
                              $this->conditionDtoSessionKey);
        $tempAction = preg_replace($patterns,$replacements,$tempAction);

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
        if(!file_put_contents($srcFile,$tempContent,LOCK_EX)){
            S2Base_CommandUtil::showException(new Exception("Cannot write to file [ $srcFile ]"));
        } else {
            print "[INFO ] modify : $srcFile" . PHP_EOL;
        }
    }

    protected function prepareHtmlFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . S2BASE_PHP5_ZF_TPL_SUFFIX;
        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/pager/html_header.php");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeleton/pager/html.php");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/pager/html_footer.php");
        }

        $patterns = array("/@@MODULE_NAME@@/",
                          "/@@ACTION_NAME@@/",
                          "/@@PROPERTY_ROWS_TITLE@@/",
                          "/@@PROPERTY_ROWS@@/");
        $replacements = array($this->moduleName,
                              $this->actionName,
                              $this->getPropertyRowsTitle(),
                              $this->getPropertyRowsHtml());
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    protected function prepareHtmlFileWithoutDao(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_VIEW_DIR
                 . $this->actionName
                 . S2BASE_PHP5_ZF_TPL_SUFFIX; 

        $tempContent = '';
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/pager/html_header.php");
        }
        $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                      . "/skeleton/pager/html_without_dao.php");
        if (!defined('S2BASE_PHP5_LAYOUT')) {
            $tempContent .= S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                          . "/skeleton/pager/html_footer.php");
        }

        $patterns = array("/@@MODULE_NAME@@/","/@@ACTION_NAME@@/");
        $replacements = array($this->moduleName,$this->actionName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceClassFile(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/pager/service.php');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);
        if ($this->serviceInterfaceName == $this->moduleServiceInterfaceName) {
            $implementsInterface = $this->serviceInterfaceName;
        } else {
            $implementsInterface = $this->serviceInterfaceName . ', ' . $this->moduleServiceInterfaceName;
        }
        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@INTERFACE_NAME@@/",
                          "/@@DAO_NAME@@/",
                          "/@@DAO_PROPERTY@@/",
                          "/@@CONDITION_DTO_NAME@@/");
        $replacements = array($this->serviceClassName,
                              $implementsInterface,
                              $this->daoInterfaceName,
                              $daoProp,
                              $this->conditionDtoClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceClassFileWithoutDao(){
        $actionName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/pager/service_without_dao.php');
        if ($this->serviceInterfaceName == $this->moduleServiceInterfaceName) {
            $implementsInterface = $this->serviceInterfaceName;
        } else {
            $implementsInterface = $this->serviceInterfaceName . ', ' . $this->moduleServiceInterfaceName;
        }
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->serviceClassName,$implementsInterface);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/pager/service_interface.php');

        $patterns = array("/@@CLASS_NAME@@/");
        $replacements = array($this->serviceInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        CmdCommand::writeFile($srcFile,$tempContent);
    }

    protected function prepareDaoFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/pager/dao.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/","/@@CONDITION_DTO_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName, $this->conditionDtoClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    protected function prepareServiceDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/pager/service_dicon.php');

        $patterns = array("/@@SERVICE_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array($this->serviceClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    protected function prepareConditionDtoFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_ENTITY_DIR
                 . $this->conditionDtoClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_PLUGIN_ZF
                     . '/skeleton/pager/condition_dto.php');
        $patterns = array("/@@CONDITION_DTO_NAME@@/");
        $replacements = array($this->conditionDtoClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function showMethodDefinitionMessage(){
        $ref = new ReflectionClass($this->daoInterfaceName);
        if (!$ref->hasMethod('findByConditionDtoList')) {
            print PHP_EOL;
            print "!!!  Please add this method definition to {$this->daoInterfaceName} interface." . PHP_EOL;
            print "!!!      public function findByConditionDtoList(" . PHP_EOL;
            print "!!!                          {$this->conditionDtoClassName} \$dto);";
            print PHP_EOL;
        }
    }

    private function mergeEntityPropertyNamesFromCols() {
        foreach ($this->cols as $col) {
            array_push($this->entityPropertyNames,
                       EntityCommand::getPropertyNameFromCol($col));
        }
        $this->entityPropertyNames = array_unique($this->entityPropertyNames);
    }

    private function getEntityPropertyNames($entityClassName) {
        $beanDesc = S2Container_BeanDescFactory::getBeanDesc(new ReflectionClass($entityClassName));
        $c = $beanDesc->getPropertyDescSize();
        $props = array();
        for($i=0; $i<$c; $i++){
            $props[] = $beanDesc->getPropertyDesc($i)->getPropertyName();
        }
        return $props;
    }

    private function getPropertyRowsTitle() {
        $src = "<tr>";
        foreach ($this->entityPropertyNames as $prop) {
            $src .= "<th>";
            $src .= ucfirst($prop);
            $src .= "</th>";
        }
        return $src . "</tr>";
    }

    private function getPropertyRowsHtml() {
        $src = "<tr>";
        foreach ($this->entityPropertyNames as $prop) {
            $src .= "<td>";
            $src .= '{$row->get' . ucfirst($prop) . '()|escape}';
            $src .= "</td>";
        }
        return $src . "</tr>";
    }
}
?>
