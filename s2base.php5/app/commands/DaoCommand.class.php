<?php
class DaoCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $daoInterfaceName;
    private $entityClassName;
    private $extendsEntityClassName;
    private $isEntityExtends;
    private $cols;

    public function getName(){
        return "dao";
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

        $daos = self::getAllDaoFromCommonsDao();
        $useCommonsDao = false;
        if(count($daos) > 0){
            $useCommonsDao = S2Base_StdinManager::isYes('use commons dao ?');
        }

        if($useCommonsDao){
            $daoName = S2Base_StdinManager::getValueFromArray($daos,
                                        "dao list");
                
            if ($daoName == S2Base_StdinManager::EXIT_LABEL){
                return;
            }
            
            $this->daoInterfaceName = $daoName;
            $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
            $this->extendsEntityClassName = "none";
            $this->tableName = 'auto defined';
            $this->cols = array('auto defined');
            
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFilesWithCommonsDao();
        } else {
            try{
                $this->daoInterfaceName = S2Base_StdinManager::getValue('dao interface name ? : ');
                $this->validate($this->daoInterfaceName);

                $entityClassNameTmp = self::guessDaoName($this->daoInterfaceName);
                $this->entityClassName = S2Base_StdinManager::getValue("entity class name ? [$entityClassNameTmp] : ");
                if(trim($this->entityClassName) == ''){
                    $this->entityClassName = $entityClassNameTmp;
                }
                $this->validate($this->entityClassName);
/*
                $this->tableName = S2Base_StdinManager::getValue("table name ? [{$this->entityClassName}] : ");
                if(trim($this->tableName) == ''){
                    $this->tableName = $this->entityClassName;
                }
                $this->validate($this->tableName);
*/
            } catch(Exception $e) {
                CmdCommand::showException($e);
                return;
            }

            $entitys = EntityCommand::getAllEntityFromCommonsDao();
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
                $this->tableName = "extended";
            } else {
                $tableNameTmp = EntityCommand::guessTableName($this->entityClassName);
                $this->tableName = S2Base_StdinManager::getValue("table name ? [{$tableNameTmp}] : ");
                if(trim($this->tableName) == ''){
                    $this->tableName = $tableNameTmp;
                }
                
                try{
                    $this->validate($this->tableName);
                } catch(Exception $e) {
                    CmdCommand::showException($e);
                    return;
                }
            }
            
            $cols = S2Base_StdinManager::getValue("columns ? (id,name,--,,) : ");
            $this->cols = explode(',',$cols);

            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        }
    }

    public static function getAllDaoFromCommonsDao(){
        $commonsDaoDir = S2BASE_PHP5_ROOT . '/app/commons/dao';
        $entries = scandir($commonsDaoDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $commonsDaoDir ]");
        }
        $daos = array();
        foreach($entries as $entry){
            $maches = array();
            if(preg_match("/(\w+Dao)\.class\.php$/",$entry,$maches)){
                $daos[] = $maches[1];
            }
        }
        return $daos;
    }

    private function getCmdMessage(){
        if ($this->entityClassName == null){
            return 'entity class name ? : ';
        }else{
            return "entity class name ? [$this->entityClassName] : ";
        }
    }

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    private function finalConfirm(){
        print "\n[ generate information ] \n";
        print "  module name          : {$this->moduleName} \n";
        print "  dao interface name   : {$this->daoInterfaceName} \n";
        print "  dao test class name  : {$this->daoInterfaceName}Test \n";
        print "  entity class name    : {$this->entityClassName} \n";
        print "  entity class extends : {$this->extendsEntityClassName} \n";
        print "  table name           : {$this->tableName} \n";
        $cols = implode(', ',$this->cols);
        print "  columns              : $cols \n";
        print "  dao dicon file name  : {$this->daoInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";

        return S2Base_StdinManager::isYes('confirm ?');
    }

    private function prepareFiles(){
        $this->prepareDaoFile();
        $this->prepareDaoTestFile();
        $this->prepareDiconFile();
        $this->prepareEntityFile();
    }

    private function prepareFilesWithCommonsDao(){
        $this->prepareDaoTestFile();
        $this->prepareDiconFile();
    }
    
    private function prepareDaoFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DAO_DIR . 
                   "{$this->daoInterfaceName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dao/dao.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareDaoTestFile(){
        $testName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR . 
                    $this->moduleName . 
                    S2BASE_PHP5_DAO_DIR . 
                    "$testName.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dao/test.php');
        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@DAO_CLASS@@/");
        $replacements = array($testName,$this->moduleName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->daoInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dao/dicon.php');
        $tempContent = preg_replace("/@@DAO_CLASS@@/",
                                    $this->daoInterfaceName,
                                    $tempContent);   
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareEntityFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_ENTITY_DIR . 
                   "{$this->entityClassName}.class.php";
        $accessorSrc = EntityCommand::getAccessorSrc($this->cols);
        $toStringSrc = EntityCommand::getToStringSrc($this->cols);
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
    
    public static function guessDaoName($name){
        $patterns = array("/Dao$/");
        $replacements = array('Entity');
        $guess = preg_replace($patterns,$replacements,$name);
        return $guess == $name ? $name . 'Entity' : $guess;
    }
}
?>