<?php
require_once('AgaviCommandUtil.class.php');
require_once(S2BASE_PHP5_ROOT . '/app/commands/EntityCommand.class.php');
class AgaviDaoCommand implements S2Base_GenerateCommand
{
    private $pathName   = S2BASE_PHP5_AG_DEFAULT_PATH;
    private $moduleName = S2BASE_PHP5_AG_DEFAULT_MODULE;
    private $moduleDir;
    private $daoInterfaceName;
    private $entityClassName;
    private $cols;

    public function getName(){
        return "<agavi> dao";
    }

    public function execute ()
    {
        $pathName = AgaviCommandUtil::getValueFromType(S2BASE_PHP5_AG_TYPE_PATH);
        if (strlen($pathName) > 0)
        {
            $this->pathName = $pathName;
        }
        $targetDir = $this->pathName . S2BASE_PHP5_AG_MODULE_DIR;
        $this->moduleName = AgaviCommandUtil::getModuleName($targetDir);
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        $this->moduleDir = $targetDir . S2BASE_PHP5_DS . $this->moduleName;

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

        $srcFile = $this->pathName .
                   S2BASE_PHP5_AG_MODULE_DIR .
                   S2BASE_PHP5_DS .
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
        $testFile = $this->pathName .
                    S2BASE_PHP5_AG_TEST_DIR . 
                    $this->moduleName . 
                    S2BASE_PHP5_DAO_DIR . 
                    "$testName.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_AG_SKELETON_DIR .
                                                 'agavi_dao_test.php');
        $tempContent = preg_replace("/@@AG_PROJECT_DIR@@/",
                                    $this->pathName,
                                    $tempContent); 
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
        $srcFile = $this->pathName .
                   S2BASE_PHP5_AG_MODULE_DIR . 
                   S2BASE_PHP5_DS .
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

        $srcFile = $this->pathName .
                   S2BASE_PHP5_AG_MODULE_DIR . 
                   S2BASE_PHP5_DS . 
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