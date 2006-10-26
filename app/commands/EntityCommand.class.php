<?php
require_once('DefaultCommandUtil.class.php');
class EntityCommand implements S2Base_GenerateCommand {

    protected $moduleName;
    protected $entityClassName;
    protected $extendsEntityClassName;
    protected $entityExtends;
    protected $cols;
    protected $useDB;

    public static function getAccessorSrc($cols){
        $tempContent  = '    protected $@@PROP_NAME@@;' . "\n" .
                        '    const @@PROP_NAME@@_COLUMN = "@@COL_NAME@@";' . "\n" .
                        '    public function set@@UC_PROP_NAME@@($val){$this->@@PROP_NAME@@ = $val;}' . "\n" . 
                        '    public function get@@UC_PROP_NAME@@(){return $this->@@PROP_NAME@@;}' . "\n\n";
        $retSrc = "";
        foreach($cols as $col){
            $prop = self::getPropertyNameFromCol($col);
            
            $patterns = array("/@@UC_PROP_NAME@@/","/@@PROP_NAME@@/","/@@COL_NAME@@/");
            $replacements = array(ucfirst($prop),$prop,$col);
            $retSrc .= preg_replace($patterns,$replacements,$tempContent);
        }
        return $retSrc;
    }

    public static function getPropertyNameFromCol($col){
        $prop = strtolower($col);
        if (preg_match("/_/",$col)){
            $prop = preg_replace("/_/"," ",$prop);
            $prop = ucwords($prop);
            $prop = preg_replace("/\s+/","",$prop);
            $prop = strtolower(substr($prop,0,1)) . substr($prop,1);
        }
        return $prop;
    }

    public static function getToStringSrc($cols){
        
        if (count($cols) == 0){
            return "";
        }
        
        $src      = '    public function __toString() {' . "\n";
        $src     .= '        $buf = array();' . "\n";
        foreach($cols as $col){
            $prop = self::getPropertyNameFromCol($col);
            $getter = '\' . $this->get' . ucfirst($prop) . '();';            
            $src .= '        $buf[] = \'' . "$prop => " . $getter . "\n";
        }
        $src     .= '        return \'{\' . implode(\', \',$buf) . \'}\';' . "\n";
        $src     .= '    }' . "\n";
        return $src;
    }

    public static function guessTableName($name){
        $patterns = array("/Entity$/","/Dto$/","/Bean$/");
        $replacements = array('','','');
        $guess = strtoupper(preg_replace($patterns,$replacements,$name));
        return $guess == $name ? strtoupper($name) : $guess;
    }

    public static function validateCols($colsStr){
        $colsTmp = array_unique(explode(',',$colsStr));
        $cols = array();
        foreach($colsTmp as $col){
            $col = trim($col);
            if(preg_match("/^\w+$/",$col)){
                $cols[] = $col;
            }
        }
        return $cols;
    }

    public static function isCommonsEntityAvailable() {
        $entitys = self::getAllEntityFromCommonsDao();
        if(count($entitys) > 0){
            return S2Base_StdinManager::isYes('extends commons entity ?');
        } else {
            return false;
        }
    }

    public static function getAllEntityFromCommonsDao(){
        $commonsDaoDir = S2BASE_PHP5_ROOT . '/app/commons/dao';
        $entries = scandir($commonsDaoDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $commonsDaoDir ]");
        }
        $entitys = array();
        foreach($entries as $entry){
            $maches = array();
            if(preg_match("/(\w+Entity)\.class\.php$/",$entry,$maches)){
                $entitys[] = $maches[1];
            }
        }
        return $entitys;
    }

    public function getName(){
        return "entity";
    }

    public function execute(){
        try{
            $this->moduleName = DefaultCommandUtil::getModuleName();
            if(DefaultCommandUtil::isListExitLabel($this->moduleName)){
                return;
            }

            $this->useDB = $this->isUseDB();
            if ($this->useDB) {
                if ($this->getEntityInfoFromDB() and 
                    $this->finalConfirm()){
                    $this->prepareFiles();
                }
            } else {
                if ($this->getEntityInfoInteractive() and
                    $this->finalConfirm()){
                    $this->prepareFiles();
                }
            }
        } catch(Exception $e) {
            DefaultCommandUtil::showException($e);
            return;
        }
    }

    protected function isUseDB() {
        return S2Base_StdinManager::isYes('use database ?');
    }

    protected function isEntityExtends() {
        return EntityCommand::isCommonsEntityAvailable();
    }

    protected function getEntityInfoFromDB() {
        $dbms = DefaultCommandUtil::getS2DaoSkeletonDbms();
        $this->tableName = S2Base_StdinManager::getValueFromArray($dbms->getTables(),
                                                                  "table list");
        if (DefaultCommandUtil::isListExitLabel($this->tableName)){
            return false;
        }
        $this->entityClassName = ucfirst(strtolower($this->tableName)) . S2DaoSkelConst::BeanName;
        $this->cols = $dbms->getColumns($this->tableName);
        $this->extendsEntityClassName = "none";

        $entityClassNameTmp = S2Base_StdinManager::getValue("entity class name ? [{$this->entityClassName}] : ");
        $this->entityClassName = trim($entityClassNameTmp) == '' ? $this->entityClassName : $entityClassNameTmp;
        $this->validate($this->entityClassName);
        return true;
    }

    protected function getEntityInfoInteractive() {
        $this->entityClassName = S2Base_StdinManager::getValue('entity class name ? : ');
        $this->validate($this->entityClassName);

        $this->entityExtends = $this->isEntityExtends();
        $this->extendsEntityClassName = "none";
        if ($this->entityExtends) {
            $entitys = self::getAllEntityFromCommonsDao();
            $this->extendsEntityClassName = S2Base_StdinManager::getValueFromArray($entitys,
                                        "entity list");
            if (DefaultCommandUtil::isListExitLabel($this->extendsEntityClassName)){
                return false;
            }
            $this->tableName = "extended";
        } else {
            $tableNameTmp = self::guessTableName($this->entityClassName);
            $this->tableName = S2Base_StdinManager::getValue("table name ? [{$tableNameTmp}] : ");
            if(trim($this->tableName) == ''){
                $this->tableName = $tableNameTmp;
            }
            $this->validate($this->tableName);
        }
        $cols = S2Base_StdinManager::getValue("columns ? (id,name,--,) : ");
        $this->cols = self::validateCols($cols);

        return true;
    }

    protected function validate($name){
        DefaultCommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    protected function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name          : {$this->moduleName} \n";
        print "  entity class name    : {$this->entityClassName} \n";
        if (!$this->useDB) {
            print "  entity class extends : {$this->extendsEntityClassName} \n";
        }
        print "  table name           : {$this->tableName} \n";
        $cols = implode(', ',$this->cols);
        print "  columns              : $cols \n";

        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareEntityFile();
    }
    
    protected function prepareEntityFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR 
                 . $this->moduleName
                 . S2BASE_PHP5_ENTITY_DIR
                 . $this->entityClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $accessorSrc = self::getAccessorSrc($this->cols);
        $toStringSrc = self::getToStringSrc($this->cols);
        if ($this->entityExtends) {
            $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                         . 'entity/entity_extends.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@ACCESSOR@@/","/@@EXTENDS_CLASS@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$accessorSrc,$this->extendsEntityClassName,$toStringSrc);
        }else{
            $tempContent = DefaultCommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                         . 'entity/entity.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        DefaultCommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>