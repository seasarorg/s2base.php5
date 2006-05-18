<?php
class EntityCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $entityClassName;
    private $cols;

    public function getName(){
        return "entity";
    }

    public function execute(){
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $this->entityClassName = S2Base_StdinManager::getValue('entity class name ? : ');
        $this->validate($this->entityClassName);

        $this->tableName = S2Base_StdinManager::getValue("table name ? [{$this->entityClassName}] : ");
        if(trim($this->tableName) == ''){
            $this->tableName = $this->entityClassName;
        }
        $this->validate($this->tableName);

        $cols = S2Base_StdinManager::getValue("columns ? [id,name,--, , ] : ");
        $this->cols = explode(',',$cols);
        if (!$this->finalConfirm()){
            return;
        }
        $this->prepareFiles();
    }        

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name       : {$this->moduleName} \n";
        print "  entity class name : {$this->entityClassName} \n";
        print "  table name        : {$this->tableName} \n";
        $cols = implode(', ',$this->cols);
        print "  columns           : $cols \n";

        $types = array('yes','no');
        $rep = S2Base_StdinManager::getValueFromArray($types,
                                        "confirmation");
        if ($rep == S2Base_StdinManager::EXIT_LABEL or 
            $rep == 'no'){
            return false;
        }
        return true;
    }

    private function prepareFiles(){
        $this->prepareEntityFile();
    }
    
    private function prepareEntityFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_ENTITY_DIR . 
                   "{$this->entityClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'entity.php');
        $src = $this->getAccessorSrc($this->cols);

        $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/");
        $replacements = array($this->entityClassName,$this->tableName,$src);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        CmdCommand::writeFile($srcFile,$tempContent);
    }

    public static function getAccessorSrc($cols){
        $src = null;
        $setter = '    private $@@NAME@@;' . "\n" .
                  '    public function set@@UC_NAME@@($val){$this->@@NAME@@ = $val;}';
        $getter = '    public function get@@UC_NAME@@(){return $this->@@NAME@@;}';
        $cols = array_unique($cols);
        foreach($cols as $col){
            $col = trim($col);
            if(!preg_match("/^\w+$/",$col)){
                continue;
            }
            $tmp = preg_replace("/@@UC_NAME@@/",ucfirst($col),$setter);
            $src .= preg_replace("/@@NAME@@/",$col,$tmp) . "\n";
            $tmp = preg_replace("/@@UC_NAME@@/",ucfirst($col),$getter);
            $src .= preg_replace("/@@NAME@@/",$col,$tmp) . "\n\n";
        }
 
        return $src;
    }
}
?>