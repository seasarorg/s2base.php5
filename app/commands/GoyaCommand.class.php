<?php
class GoyaCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $serviceName;
    private $serviceClassName;
    private $serviceInterfaceName;
    private $daoClassName;
    private $entityClassName;
    private $tableName;
    private $cols;
    
    public function getName(){
        return "goya";
    }

    public function execute(){
        $this->moduleName = S2Base_CommandUtil::getModuleName();
        if($this->moduleName == S2Base_StdinManager::EXIT_LABEL){
            return;
        }

        $name = S2Base_StdinManager::getValue('service name ? : ');
        $this->validate($name);

        $types = array('yes','no');
        $rep = S2Base_StdinManager::getValueFromArray($types,
                                        "use commons dao ?");
        if ($rep == S2Base_StdinManager::EXIT_LABEL){
            return;
        }
        if($rep == 'yes'){
            $daoName = $this->getDaoFromCommonsDao();
            if ($daoName == S2Base_StdinManager::EXIT_LABEL){
                return;
            }
            $this->setServiceNameWithCommonsDao($name,$daoName);
            $this->prepareFilesWithCommonsDao();
            return;
        }

        $this->setServiceName($name);
        $this->tableName = S2Base_StdinManager::getValue("table name ? [{$name}] : ");
        if(trim($this->tableName) == ''){
            $this->tableName = $name;
        }
        $this->validate($this->tableName);

        $cols = S2Base_StdinManager::getValue("columns ? [id,name,--, , ] : ");
        $this->cols = explode(',',$cols);
        $this->prepareFiles();
    }        

    private function getDaoFromCommonsDao(){
        $commonsDaoDir = S2BASE_PHP5_ROOT . '/app/commons/dao';
        $entries = scandir($commonsDaoDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $commonsDaoDir ]");
        }
        $daos = array();
        foreach($entries as $entry){
            if(preg_match("/(\w+Dao)\.class\.php$/",$entry,$maches)){
                $daos[] = $maches[1];
            }
        }
        if(count($daos) == 0){
            throw new Exception("dao not found at all in [ $commonsDaoDir ]");
        }

        $daoName = S2Base_StdinManager::getValueFromArray($daos,
                                        "dao list");
        return $daoName;
    }

    private function setServiceName($name){
        $this->serviceName = $name;
        $name = ucfirst($name);
        $this->serviceInterfaceName = $name . "Service";
        $this->serviceClassName = $name . "ServiceImpl";
        $this->daoClassName = $name . "Dao";
        $this->entityClassName = $name . "Entity";
    }

    private function setServiceNameWithCommonsDao($name,$daoName){
        $this->serviceName = $name;
        $name = ucfirst($name);
        $this->serviceInterfaceName = $name . "Service";
        $this->serviceClassName = $name . "ServiceImpl";
        $this->daoClassName = $daoName;
        $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
    }
    
    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid service name. [ $name ]");
    }
    
    private function prepareFiles(){
        $this->prepareServiceClassFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDaoFile();
        $this->prepareDaoTestFile();
        $this->prepareEntityFile();
        $this->prepareDiconFile();
    }

    private function prepareFilesWithCommonsDao(){
        $this->prepareServiceClassFile();
        $this->prepareServiceInterfaceFile();
        $this->prepareServiceTestFile();
        $this->prepareDaoTestFile();
        $this->prepareDiconFile();
    }
    
    private function prepareServiceClassFile(){
        $serviceName = $this->serviceClassName . "Impl";
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_SERVICE_DIR . 
                   "{$this->serviceClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'goya_service.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceClassName,
                             $tempContent);   
        $tempContent = preg_replace("/@@INTERFACE_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        $tempContent = preg_replace("/@@DAO_NAME@@/",
                             $this->daoClassName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }

    private function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_SERVICE_DIR . 
                   "{$this->serviceInterfaceName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'service_interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }

    private function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $testFile = S2BASE_PHP5_TEST_MODULES_DIR . 
                    $this->moduleName . 
                    S2BASE_PHP5_SERVICE_DIR . 
                    "$testName.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'goya_service_test.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $testName,
                             $tempContent);   
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                             $this->moduleName,
                             $tempContent);   
        $tempContent = preg_replace("/@@SERVICE_NAME@@/",
                             $this->serviceName,
                             $tempContent);   
        $tempContent = preg_replace("/@@SERVICE_INTERFACE@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($testFile,$tempContent);       
        print "[INFO ] create : $testFile\n";
    }

    private function prepareDaoFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DAO_DIR . 
                   "{$this->daoClassName}.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'goya_dao.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->daoClassName,
                             $tempContent);   
        $tempContent = preg_replace("/@@ENTITY_NAME@@/",
                             $this->entityClassName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }

    private function prepareDaoTestFile(){
        $testClassName = $this->daoClassName . "Test";
        $testFile = S2BASE_PHP5_TEST_MODULES_DIR . 
                    $this->moduleName . 
                    S2BASE_PHP5_DAO_DIR . 
                    "$testClassName.class.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'goya_dao_test.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $testClassName,
                             $tempContent);   
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                             $this->moduleName,
                             $tempContent);   
        $tempContent = preg_replace("/@@DAO_CLASS@@/",
                             $this->daoClassName,
                             $tempContent);   
        $tempContent = preg_replace("/@@SERVICE_INTERFACE@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        $tempContent = preg_replace("/@@SERVICE_NAME@@/",
                             $this->serviceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($testFile,$tempContent);       
        print "[INFO ] create : $testFile\n";
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

    private function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->serviceInterfaceName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR .
                                                 'goya_dicon.php');
        $tempContent = preg_replace("/@@SERVICE_NAME@@/",
                                    $this->serviceName,
                                    $tempContent);   
        $tempContent = preg_replace("/@@SERVICE_CLASS@@/",
                                    $this->serviceClassName,
                                    $tempContent);   
        $tempContent = preg_replace("/@@DAO_CLASS@@/",
                                    $this->daoClassName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
        print "[INFO ] create : $srcFile\n";      
    }
}
?>