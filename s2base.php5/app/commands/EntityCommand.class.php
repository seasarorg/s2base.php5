<?php
class EntityCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $entityClassName;
    private $extendsEntityClassName;
    private $isEntityExtends;
    private $cols;

    public function getName(){
        return "entity";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        try{
            $this->entityClassName = S2Base_StdinManager::getValue('entity class name ? : ');
            $this->validate($this->entityClassName);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }
            
        $entitys = self::getAllEntityFromCommonsDao();
        $this->isEntityExtends = false;
        if(count($entitys) > 0){
            $this->isEntityExtends = S2Base_StdinManager::isYes('extends commons entity ?');
        }

        $this->extendsEntityClassName = "none";
        if ($this->isEntityExtends) {
            $this->extendsEntityClassName = S2Base_StdinManager::getValueFromArray($entitys,
                                            "entity list");
            if ($this->extendsEntityClassName == S2Base_StdinManager::EXIT_LABEL){
                return;
            }
        }

        try{
            if (!$this->isEntityExtends) {
                $tableNameTmp = self::guessTableName($this->entityClassName);
                $this->tableName = S2Base_StdinManager::getValue("table name ? [{$tableNameTmp}] : ");
                if(trim($this->tableName) == ''){
                    $this->tableName = $this->entityClassName;
                }
                $this->validate($this->tableName);
            } else {
                $this->tableName = "extended";
            }
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }

        $cols = S2Base_StdinManager::getValue("columns ? (id,name,--,,) : ");
        $this->cols = explode(',',$cols);
        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
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

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name          : {$this->moduleName} \n";
        print "  entity class name    : {$this->entityClassName} \n";
        print "  entity class extends : {$this->extendsEntityClassName} \n";
        print "  table name           : {$this->tableName} \n";
        $cols = implode(', ',$this->cols);
        print "  columns              : $cols \n";

        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function prepareFiles(){
        $this->prepareEntityFile();
    }
    
    private function prepareEntityFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_ENTITY_DIR . 
                   "{$this->entityClassName}.class.php";
        $accessorSrc = self::getAccessorSrc($this->cols);
        $toStringSrc = self::getToStringSrc($this->cols);
        if ($this->isEntityExtends) {
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                         . 'entity/entity_extends.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@ACCESSOR@@/","/@@EXTENDS_CLASS@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$accessorSrc,$this->extendsEntityClassName,$toStringSrc);
        }else{
            $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                         . 'entity/entity.php');
            $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/","/@@TO_STRING@@/");
            $replacements = array($this->entityClassName,$this->tableName,$accessorSrc,$toStringSrc);
        }

        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    public static function getAccessorSrc($cols){
        $tempContent  = '    private $@@PROP_NAME@@;' . "\n" .
                        '    const @@PROP_NAME@@_COLUMN = "@@COL_NAME@@";' . "\n" .
                        '    public function set@@UC_PROP_NAME@@($val){$this->@@PROP_NAME@@ = $val;}' . "\n" . 
                        '    public function get@@UC_PROP_NAME@@(){return $this->@@PROP_NAME@@;}' . "\n\n";
        $cols = array_unique($cols);
        $retSrc = "";
        foreach($cols as $col){
            $col = trim($col);
            if(!preg_match("/^\w+$/",$col)){
                continue;
            }
            $prop = preg_replace("/_/"," ",strtolower($col));
            $prop = ucwords($prop);
            $prop = preg_replace("/\s+/","",$prop);
            $prop = strtolower(substr($prop,0,1)) . substr($prop,1);
            
            $patterns = array("/@@UC_PROP_NAME@@/","/@@PROP_NAME@@/","/@@COL_NAME@@/");
            $replacements = array(ucfirst($prop),$prop,$col);
            $retSrc .= preg_replace($patterns,$replacements,$tempContent);
        }
        return $retSrc;
    }

    public static function getToStringSrc($cols){
        
        if (count($cols) == 0){
            return "";
        }
        
        $src    = '    public function __toString() {' . "\n";
        $src   .= '        $buf = array();' . "\n";
        $src   .= '        foreach ($this as $key => $val) {' . "\n";
        $src   .= '            if (is_array($val)) {' . "\n";
        $src   .= '            } else if (is_object($val)) {' . "\n";
        $src   .= '            } else {' . "\n";
        $src   .= '                $buf[] = "$key => $val";' . "\n";
        $src   .= '            }' . "\n";
        $src   .= '        }' . "\n";
        $src   .= '        return "{" . implode(", ",$buf) . "}";' . "\n";
        $src   .= '    }' . "\n";
        return $src;
    }
    
    public static function guessTableName($name){
        $patterns = array("/Entity$/","/Dto$/","/Bean$/");
        $replacements = array('','','');
        $guess = strtoupper(preg_replace($patterns,$replacements,$name));
        return $guess == $name ? strtoupper($name) : $guess;
    }
}
?>