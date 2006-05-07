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
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $this->daoInterfaceName = S2Base_StdinManager::getValue('dao interface name ? : ');
        $this->validate($this->daoInterfaceName);

        $this->entityClassName = S2Base_StdinManager::getValue('entity class name ? : ');
        $this->validate($this->entityClassName);

        $this->tableName = S2Base_StdinManager::getValue("table name ? [{$this->entityClassName}] : ");
        if(trim($this->tableName) == ''){
            $this->tableName = $this->entityClassName;
        }
        $this->validate($this->tableName);

        $cols = S2Base_StdinManager::getValue("columns ? [id,name,--, , ] : ");
        $this->cols = explode(',',$cols);

        $this->prepareFiles();

    }

    private function getCmdMessage(){
        if ($this->entityClassName == null){
            return 'entity class name ? : ';
        }else{
            return "entity class name ? [$this->entityClassName] : ";
        }
    }

    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid name. [ $name ]");
    }

    private function prepareFiles(){
        $this->prepareDaoFile();
        $this->prepareDaoTestFile();
        $this->prepareDiconFile();
        $this->prepareEntityFile();
    }
    
    private function prepareDaoFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DAO_DIR . 
                   "{$this->daoInterfaceName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'dao.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->daoInterfaceName,
                             $tempContent);   
        $tempContent = preg_replace("/@@ENTITY_NAME@@/",
                             $this->entityClassName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }

    private function prepareDaoTestFile(){
        $testName = $this->daoInterfaceName . "Test";
        $testFile = S2BASE_PHP5_TEST_MODULES_DIR . 
                    $this->moduleName . 
                    S2BASE_PHP5_DAO_DIR . 
                    "$testName.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'dao_test.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $testName,
                             $tempContent);   
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                             $this->moduleName,
                             $tempContent);   
        $tempContent = preg_replace("/@@DAO_CLASS@@/",
                             $this->daoInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($testFile,$tempContent);       
        print "[INFO ] create : $testFile\n";
    }

    private function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->daoInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'dao_dicon.php');
        $tempContent = preg_replace("/@@DAO_CLASS@@/",
                                    $this->daoInterfaceName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }

    private function prepareEntityFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_ENTITY_DIR . 
                   "{$this->entityClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'entity.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->entityClassName,
                             $tempContent);   
        $tempContent = preg_replace("/@@TABLE_NAME@@/",
                             $this->tableName,
                             $tempContent);   

        $src = EntityCommand::getAccessorSrc($this->cols);
        $tempContent = preg_replace("/@@ACCESSOR@@/",
                             $src,
                             $tempContent);   

        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }
}
?>