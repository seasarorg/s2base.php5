<?php
class DaoCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $daoInterfaceName;
    private $entityClassName;
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

        $rep = S2Base_StdinManager::isYes('use commons dao ?');

        if($rep){
            try{
                $daoName = GoyaCommand::getDaoFromCommonsDao();
            } catch(Exception $e) {
                CmdCommand::showException($e);
                return;
            }
                
            if ($daoName == S2Base_StdinManager::EXIT_LABEL){
                return;
            }
            
            $this->daoInterfaceName = $daoName;
            $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
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

                $this->entityClassName = S2Base_StdinManager::getValue('entity class name ? : ');
                $this->validate($this->entityClassName);

                $this->tableName = S2Base_StdinManager::getValue("table name ? [{$this->entityClassName}] : ");
                if(trim($this->tableName) == ''){
                    $this->tableName = $this->entityClassName;
                }
                $this->validate($this->tableName);
            } catch(Exception $e) {
                CmdCommand::showException($e);
                return;
            }
            $cols = S2Base_StdinManager::getValue("columns ? (id,name,--,) : ");
            $this->cols = explode(',',$cols);

            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        }
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
        print "  module name         : {$this->moduleName} \n";
        print "  dao interface name  : {$this->daoInterfaceName} \n";
        print "  dao test class name : {$this->daoInterfaceName}Test \n";
        print "  entity class name   : {$this->entityClassName} \n";
        print "  table name          : {$this->tableName} \n";
        $cols = implode(', ',$this->cols);
        print "  columns             : $cols \n";
        print "  dao dicon file name : {$this->daoInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";

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
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'dao/entity.php');
        $src = EntityCommand::getAccessorSrc($this->cols);

        $patterns = array("/@@CLASS_NAME@@/","/@@TABLE_NAME@@/","/@@ACCESSOR@@/");
        $replacements = array($this->entityClassName,$this->tableName,$src);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);

        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>