<?php
class DefaultCommandUtil {

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
            print "[INFO ] create : $srcFile\n";
        }catch(Exception $e){
            if ($e instanceof S2Base_FileExistsException){
                print "[INFO ] exists : $srcFile\n";
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
            print "[INFO ] create : $dirPath\n";
        }catch(Exception $e){
            if ($e instanceof S2Base_FileExistsException){
                print "[INFO ] exists : $dirPath\n";
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
        print "\n!!! Exception\n!!! {$e->getMessage()}\n\n";
    }
}
?>
