<?php
class LogicCommand implements S2Base_GenerateCommand {
    const LOGIC_DIR = '/logic/';

    protected $moduleName;
    protected $logicInterfaceName;
    protected $logicClassName;
    
    public function getName(){
        return "logic";
    }

    public function execute(){
        try{
            $this->moduleName = S2Base_CommandUtil::getModuleName();
            if(S2Base_CommandUtil::isListExitLabel($this->moduleName)){
                return;
            }

            $this->logicInterfaceName = S2Base_StdinManager::getValue('logic interface name ? : ');
            $this->validate($this->logicInterfaceName);

            $this->logicClassName = $this->logicInterfaceName . "Impl";
            $logicClassNameTmp = S2Base_StdinManager::getValue("logic class name ? [{$this->logicClassName}] : ");
            if(trim($logicClassNameTmp) != ''){
                $this->logicClassName = $logicClassNameTmp;
            }
            $this->validate($this->logicClassName);

            if (!$this->finalConfirm()){
                return;
            }
            $this->prepareFiles();
        } catch(Exception $e) {
            S2Base_CommandUtil::showException($e);
            return;
        }
    }

    protected function validate($name){
        S2Base_CommandUtil::validate($name,"Invalid logic name. [ $name ]");
    }

    protected function finalConfirm(){
        print PHP_EOL . '[ generate information ]' . PHP_EOL;
        print "  module name           : {$this->moduleName}" . PHP_EOL;
        print "  logic interface name  : {$this->logicInterfaceName}" . PHP_EOL;
        print "  logic class name      : {$this->logicClassName}" . PHP_EOL;
        print "  logic test class name : {$this->logicClassName}Test" . PHP_EOL;
        print "  logic dicon file name : {$this->logicClassName}" . S2BASE_PHP5_DICON_SUFFIX . PHP_EOL;
        return S2Base_StdinManager::isYes('confirm ?');
    }

    protected function prepareFiles(){
        $this->prepareLogicDir();
        $this->prepareLogicImplFile();
        $this->prepareLogicInterfaceFile();
        $this->prepareLogicTestFile();
        $this->prepareDiconFile();
    }

    protected function prepareLogicDir(){
        $dir = S2BASE_PHP5_MODULES_DIR 
             . $this->moduleName
             . self::LOGIC_DIR;
        S2Base_CommandUtil::createDirectory($dir);
        $dir = S2BASE_PHP5_TEST_MODULES_DIR 
             . $this->moduleName
             . self::LOGIC_DIR;
        S2Base_CommandUtil::createDirectory($dir);
    }
    
    protected function prepareLogicImplFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . self::LOGIC_DIR
                 . $this->logicClassName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'logic/logic.php');

        $patterns = array("/@@CLASS_NAME@@/","/@@INTERFACE_NAME@@/");
        $replacements = array($this->logicClassName,$this->logicInterfaceName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareLogicInterfaceFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . self::LOGIC_DIR
                 . $this->logicInterfaceName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'logic/interface.php');
        $tempContent = preg_replace("/@@CLASS_NAME@@/",
                             $this->logicInterfaceName,
                             $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareLogicTestFile(){
        $testName = $this->logicClassName . "Test";
        $srcFile = S2BASE_PHP5_TEST_MODULES_DIR
                 . $this->moduleName
                 . self::LOGIC_DIR
                 . $testName
                 . S2BASE_PHP5_CLASS_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'logic/test.php');

        $patterns = array("/@@CLASS_NAME@@/",
                          "/@@MODULE_NAME@@/",
                          "/@@LOGIC_INTERFACE@@/",
                          "/@@LOGIC_CLASS@@/");
        $replacements = array($testName,
                              $this->moduleName,
                              $this->logicInterfaceName,
                              $this->logicClassName);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }

    protected function prepareDiconFile(){
        $srcFile = S2BASE_PHP5_MODULES_DIR
                 . $this->moduleName
                 . S2BASE_PHP5_DICON_DIR 
                 . $this->logicClassName
                 . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SKELETON_DIR
                     . 'logic/dicon.php');
        $tempContent = preg_replace("/@@LOGIC_CLASS@@/",
                                    $this->logicClassName,
                                    $tempContent);   
        S2Base_CommandUtil::writeFile($srcFile,$tempContent);
    }
}
?>
