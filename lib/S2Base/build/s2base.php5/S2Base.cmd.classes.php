<?php
class S2Base_CommandLauncherFactory {
    public static function create($classFiles) {
        $launcher = new S2Base_CommandLauncher();
        foreach ($classFiles as $classFile) {
            $fileInfo = pathinfo($classFile);
            if (strtolower($fileInfo['extension']) == 'php' and 
                preg_match("/^(\w+)/",$fileInfo['basename'],$matches)) {
                $cmdClassName = $matches[1];
                if (!class_exists($cmdClassName,false)) {
                    require_once($classFile);
                }
                if (!class_exists($cmdClassName,false)) {
                    continue;
                }
                $ref = new ReflectionClass($cmdClassName);
                if (!$ref->isAbstract() and 
                    !$ref->isInterface() and
                    $ref->isSubclassOf('S2Base_GenerateCommand')) {
                    $launcher->addCommand(new $cmdClassName());
                }
            }
        }
        return $launcher;
    }
}

class S2Base_CommandLauncher {
    private $commands = array();
    public function addCommand(S2Base_GenerateCommand $command){
        $this->commands[$command->getName()] = $command;
    }
    public function main(){
        $cmds = array_keys($this->commands);
        sort($cmds);
        while(true){
            $cmd = S2Base_StdinManager::getValueFromArray($cmds,"Command list");
            if($cmd == S2Base_StdinManager::EXIT_LABEL){
                break;
            }else{
                $this->commands[$cmd]->execute();
            }
        }
    }
}

class S2Base_CommandUtil {
    public static function readFile($filePath){
        if(!is_readable($filePath)){
            throw new Exception("Cannot read file [ $filePath ]");
        }
        return file_get_contents($filePath);
    }
    public static function getModuleName(){
        $modules = self::getAllModules();
        if(count($modules) == 0){
            throw new Exception("Module not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,"Module list");
    }
    public static function getAllModules(){
        $modulesDir = S2BASE_PHP5_APP_DIR . "modules";
        $entries = scandir($modulesDir);
        if(!$entries){
            throw new Exception("invalid dir : [ $modulesDir ]");
        }
        $modules = array();
        foreach($entries as $entry) {
            $path = S2BASE_PHP5_MODULES_DIR . $entry;
            if(!preg_match("/^\./",$entry) and is_dir($path)){
                array_push($modules,$entry);
            }
        }
        return $modules;
    }
    public static function validate($target, $exceptionMsg) {
        if(!preg_match("/^\w+$/",$target)){
           throw new Exception($exceptionMsg);
        }   
    }
    public static function isListExitLabel($label) {
        return $label == S2Base_StdinManager::EXIT_LABEL;
    }
    public static function getS2DaoSkeletonDbms() {
        $container = S2ContainerFactory::create(PDO_DICON);
        $cd = $container->getComponentDef('dataSource');
        $dsn = $cd->getPropertyDef('dsn')->getValue();
        if ($cd->hasPropertyDef('user')) {
            $user = $cd->getPropertyDef('user')->getValue();
        }
        if ($cd->hasPropertyDef('password')) {
            $pass = $cd->getPropertyDef('password')->getValue();
        }
        return new S2DaoSkeletonDbms($dsn, $user, $pass);
    }
    public static function writeFile($srcFile,$tempContent) {
        try{
            self::writeFileInternal($srcFile,$tempContent);
            print "[INFO ] create : $srcFile" . PHP_EOL;
        }catch(Exception $e){
            if ($e instanceof S2Base_FileExistsException){
                print "[INFO ] exists : $srcFile" . PHP_EOL;
            } else {
                throw $e;
            }
        }
    }
    public static function writeFileInternal($filePath, $contents) {
        if (file_exists($filePath)) {
            throw new S2Base_FileExistsException("Already exists. [ $filePath ]");
        }
        if(!file_put_contents($filePath,$contents,LOCK_EX)){
            throw new Exception("Cannot write to file [ $filePath ]");
        }
    }
    public static function createDirectory($dirPath){
        try{
            self::createDirectoryInternal($dirPath);
            print "[INFO ] create : $dirPath" . PHP_EOL;
        }catch(Exception $e){
            if ($e instanceof S2Base_FileExistsException){
                print "[INFO ] exists : $dirPath" . PHP_EOL;
            } else {
                throw $e;
            }
        }
    }
    public static function createDirectoryInternal($directoryPath){
        if(!file_exists($directoryPath)){
            if(!mkdir($directoryPath)){
               throw new Exception("Cannot make dir [ $directoryPath ]");
            }
            return true;
        }else{
            throw new S2Base_FileExistsException("Already exists. [ $directoryPath ]");
        }
    }
    public static function showException(Exception $e){
        print PHP_EOL . '!!! Exception' . PHP_EOL;
        print "!!! {$e->getMessage()}" . PHP_EOL . PHP_EOL;
    }
}

interface S2Base_GenerateCommand {
    public function getName();
    public function execute();
}

class S2Base_StdinManager {
    const EXIT_LABEL = "(exit)";
    public static function getValueFromArray($cmds, $title){
        $cmds = array_merge(array(self::EXIT_LABEL),$cmds);
        $number = null;
        while(true){
            print "\n[ $title ]\n";
            foreach($cmds as $key=>$module){
                print "$key : $module\n";
            }
            print "choice ? : ";
            $val = trim(fgets(STDIN));
            if(strcasecmp($val,'q') == 0){
                $number = 0;
                break;
            } else if (is_numeric($val) and
                       array_key_exists($val,$cmds)) {
                $number = $val;
                break;
            }
        }
        return $cmds[$number];
    }
    public static function getValuesFromArray($cmds, $title){
        $cmds = array_merge(array(self::EXIT_LABEL),$cmds);
        $items = null;
        while(true){
            print "\n[ $title ]\n";
            $items = array();
            foreach($cmds as $key => $val){
                print "$key : $val\n";
            }
            print "choices ? (1,2,--,) : ";
            $inputVal = trim(fgets(STDIN));
            if(strcasecmp($inputVal,'q') == 0 or
               strcasecmp($inputVal,'0') == 0 ){
                $items[] = $cmds[0];
                break;
            }
            $nums = explode(',', $inputVal);
            foreach ($nums as $num) {
                $num = trim($num);
                if (is_numeric($num) and
                    $num != '0' and
                    array_key_exists($num, $cmds)) {
                    $items[] = $cmds[$num];
                }
            }
            if (count($items) > 0) {
                break;
            }
        }
        return array_unique($items);
    }
    public static function getValue($msg){
        print "\n$msg";
        $val = trim(fgets(STDIN));
        return $val;
    }
    public static function isYes($msg){
        $ret = false;
        while(true){
            print "\n$msg (y/n) : ";
            $val = trim(fgets(STDIN));
            if(strcasecmp($val,'y') == 0){
                $ret = true;
                break;
            }else if (strcasecmp($val,'n') == 0){
                $ret = false;
                break;
            }
        }
        return $ret;
    }
}

class S2Base_FileExistsException extends Exception {
    public function __construct($msg){
        parent::__construct($msg);
    }   
}

?>
