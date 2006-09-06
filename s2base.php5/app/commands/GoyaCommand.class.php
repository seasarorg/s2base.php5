<?php
class GoyaCommand implements S2Base_GenerateCommand {

    private $moduleName;
    private $serviceName;
    private $serviceClassName;
    private $serviceInterfaceName;
    private $daoInterfaceName;
    private $entityClassName;
    private $extendsEntityClassName;
    private $isEntityExtends;
    private $tableName;
    private $cols;
    
    public function getName(){
        return "goya";
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

        $name = S2Base_StdinManager::getValue('service name ? : ');
        try{
            $this->validate($name);
        } catch(Exception $e) {
            CmdCommand::showException($e);
            return;
        }

        $daos = DaoCommand::getAllDaoFromCommonsDao();
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
            
            $this->setServiceNameWithCommonsDao($name,$daoName);
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFilesWithCommonsDao();
        }else{
            $this->setServiceName($name);

            $entitys = EntityCommand::getAllEntityFromCommonsDao();
            $this->isEntityExtends = false;
            if(count($entitys) > 0){
                $this->isEntityExtends = S2Base_StdinManager::isYes('extends commons entity ?');
            }

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
            $this->cols = EntityCommand::validateCols($cols);
            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        }
    }

    private function setServiceName($name){
        $this->serviceName = $name;
        $name = ucfirst($name);
        $this->serviceInterfaceName = $name . "Service";
        $this->serviceClassName = $name . "ServiceImpl";
        $this->daoInterfaceName = $name . "Dao";
        $this->entityClassName = $name . "Entity";
        $this->extendsEntityClassName = "none";
    }

    private function setServiceNameWithCommonsDao($name,$daoName){
        $this->serviceName = $name;
        $name = ucfirst($name);
        $this->serviceInterfaceName = $name . "Service";
        $this->serviceClassName = $name . "ServiceImpl";
        $this->daoInterfaceName = $daoName;
        $this->entityClassName = preg_replace("/Dao$/","Entity",$daoName);
        $this->tableName = 'auto defined';
        $this->cols = array('auto defined');
        $this->extendsEntityClassName = "none";
    }
    
    private function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid value. [ $name ]");
    }

    private function finalConfirm(){

        print "\n[ generate information ] \n";
        print "  module name             : {$this->moduleName} \n";
        print "  service name            : {$this->serviceName} \n";
        print "  service interface name  : {$this->serviceInterfaceName} \n";
        print "  service class name      : {$this->serviceClassName} \n";
        print "  service test class name : {$this->serviceClassName}Test \n";
        print "  dao interface name      : {$this->daoInterfaceName} \n";
        print "  dao test class name     : {$this->daoInterfaceName}Test \n";
        print "  entity class name       : {$this->entityClassName} \n";
        print "  entity class extends    : {$this->extendsEntityClassName} \n";
        print "  table name              : {$this->tableName} \n";
        $cols = implode(', ',$this->cols);
        print "  columns                 : $cols \n";
        print "  dicon file name         : {$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX ." \n";

        return S2Base_StdinManager::isYes('confirm ?');
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
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/service.php');
        $daoProp = strtolower(substr($this->daoInterfaceName,0,1)) . substr($this->daoInterfaceName,1);
        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/","/@@DAO_NAME@@/","/@@DAO_PROPERTY@@/");
        $replacements = array($this->serviceClassName,$this->serviceInterfaceName,$this->daoInterfaceName,$daoProp);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $this->serviceInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/service_interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->serviceInterfaceName,
                             $tempContent);   
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareServiceTestFile(){
        $testName = $this->serviceClassName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_SERVICE_DIR
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/service_test.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@SERVICE_INTERFACE@@/","/@@SERVICE_CLASS@@/");
        $replacements = array($testName,$this->moduleName,$this->serviceInterfaceName,$this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareDaoFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $this->daoInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/dao.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@ENTITY_NAME@@/");
        $replacements = array($this->daoInterfaceName,$this->entityClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareDaoTestFile(){
        $testClassName = $this->daoInterfaceName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DAO_DIR
                 . $testClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/dao_test.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@MODULE_NAME@@/","/@@DAO_CLASS@@/","/@@SERVICE_CLASS@@/");
        $replacements = array($testClassName,$this->moduleName,$this->daoInterfaceName,$this->serviceClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }

    private function prepareEntityFile(){

        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_ENTITY_DIR
                 . $this->entityClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
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

    private function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR . 
                   $this->moduleName . 
                   S2BASE_PHP5_DICON_DIR . 
                   "{$this->serviceClassName}" . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'goya/dicon.php');

        $patterns = array("/@@SERVICE_NAME@@/","/@@SERVICE_CLASS@@/","/@@DAO_CLASS@@/");
        $replacements = array( $this->serviceName,$this->serviceClassName,$this->daoInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }
}
?>