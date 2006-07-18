<?php
class sfCommandUtil
{
    public static $attributes = array();
    
    public static function getModuleName($targetDir){
        $modules = self::getAllModules($targetDir);
        if(count($modules) == 0){
            throw new Exception("Module not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,"sf module list");
    }

    public static function getAppName($targetDir){
        $modules = self::getAllModules($targetDir);
        if(count($modules) == 0){
            throw new Exception("Application not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,"sf application list");
    }

    public static function getAllModules($targetDir){
        $entries = scandir($targetDir);
        $modules = array();
        foreach($entries as $entry) {
            $path = $targetDir . '/' .  $entry;
            if(!preg_match("/^\./",$entry) and is_dir($path)){
                $modules[] = $entry;
            }
        }
        return $modules;
    }
    
    public static function getValueFromType ($type)
    {
        switch ($type) {
            case S2BASE_PHP5_SF__PATH:
                $msg = 'ProjectFullPath[' .
                       S2BASE_PHP5_SF_DEFAULT_PATH .
                       '] ? : ';
            break;
            case S2BASE_PHP5_SF_PROJECT:
                $msg = 'ProjectName ? : ';
            break;
            case S2BASE_PHP5_SF_APP:
                $msg = 'ApplicationName ? : ';
            break;
            case S2BASE_PHP5_SF_MODULE:
                $msg = 'ModuleName ? : ';
            break;
            default:
                throw new Exception("Type not found");
        }
        return S2Base_StdinManager::getValue($msg);
    }
    
    public static function execSfCmd ($cmd, $args, $pathName)
    {

        if (!is_dir($pathName))
        {
            self::createDirectory($pathName);
        }
        
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
        );
        
        $cwd = $pathName;
        $cmd = "php " . S2BASE_PHP5_SF_CMD . " $cmd $args";
        $process = proc_open($cmd, $descriptorspec, $pipes, $cwd);
        if (is_resource($process)) {
            fclose($pipes[0]);
            echo stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            proc_close($process);
        }
    }
    
    public static function copyMyFrontWebController ()
    {
        $mfwcName = "myFrontWebController.class.php";
        $srcFile = self::$attributes['pathName'] . S2BASE_PHP5_DS .
                   "apps"                        . S2BASE_PHP5_DS .
                   self::$attributes['appName']  . S2BASE_PHP5_DS .
                   "lib"                         . S2BASE_PHP5_DS .
                   $mfwcName;
        @unlink($srcFile);
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SF_SKELETON_DIR
                     . $mfwcName);
        CmdCommand::writeFile($srcFile,$tempContent);
    }
    
    public static function prepareModuleDiconFile ()
    {
        $srcFile = self::$attributes['pathName']   . S2BASE_PHP5_DS .
                   "apps"                          . S2BASE_PHP5_DS .
                   self::$attributes['appName']    . S2BASE_PHP5_DS .
                   "modules"                       . S2BASE_PHP5_DS .
                   self::$attributes['moduleName'] . S2BASE_PHP5_DS .
                   S2BASE_PHP5_DICON_DIR . 
                   self::$attributes['moduleName'] . S2BASE_PHP5_DICON_SUFFIX;
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SF_SKELETON_DIR .
                                                    'actions.dicon');
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                    self::$attributes['moduleName'],
                                    $tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }
    
    public static function prepareModuleAutoloadYmlFile ()
    {
        $srcFile = self::$attributes['pathName'] . S2BASE_PHP5_DS .
                   "apps"                        . S2BASE_PHP5_DS .
                   self::$attributes['appName']  . S2BASE_PHP5_DS .
                   "config"                      . S2BASE_PHP5_DS .
                   "autoload.yml";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SF_SKELETON_DIR .
                                                    'module_autoload.yml');
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                    self::$attributes['moduleName'],
                                    $tempContent);
        if (file_exists($srcFile)) {
            $tempContent = preg_replace("/autoload:/", '', $tempContent);
            file_put_contents($srcFile, $tempContent, FILE_APPEND);
        } else {
            CmdCommand::writeFile($srcFile,$tempContent);
        }
    }
    
    public static function createLogicDirectories ()
    {
        $modulePath = self::$attributes['pathName']  . S2BASE_PHP5_DS .
                      "apps"                         . S2BASE_PHP5_DS .
                      self::$attributes['appName']   . S2BASE_PHP5_DS .
                      "modules"                      . S2BASE_PHP5_DS .
                      self::$attributes['moduleName'];
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_ENTITY_DIR,
            S2BASE_PHP5_DICON_DIR,
            S2BASE_PHP5_INTERCEPTOR_DIR,
            S2BASE_PHP5_SERVICE_DIR,);
        foreach ($dirs as $dir) {
            self::createDirectory($modulePath . $dir);
        }
    }
    
    public static function createTestDirectories ()
    {
        $testDir = self::$attributes['pathName']  . S2BASE_PHP5_DS .
                   "test"                         . S2BASE_PHP5_DS .
                   self::$attributes['appName']   . S2BASE_PHP5_DS .
                   self::$attributes['moduleName'];
        self::createDirectory($testDir);
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_SERVICE_DIR);
        foreach ($dirs as $dir) {
            self::createDirectory($testDir . $dir);
        }
    }
    
    public static function prepareTestIncFile ()
    {
        $srcFile = self::$attributes['pathName']   . S2BASE_PHP5_DS .
                   "test"                          . S2BASE_PHP5_DS .
                   self::$attributes['appName']    . S2BASE_PHP5_DS .
                   self::$attributes['moduleName'] . S2BASE_PHP5_DS .
                   "test.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_SF_SKELETON_DIR .
                                                    'sf_test_inc.php');
        $patterns = array("/@@SF_ROOT_DIR@@/","/@@APP_NAME@@/","/@@MODULE_NAME@@/");
        $replacements = array(self::$attributes['pathName'],
                              self::$attributes['appName'],
                              self::$attributes['moduleName']);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($srcFile,$tempContent);
    }
    
    public static function createDirectory ($path)
    {
        if(S2Base_CommandUtil::createDirectory($path)){
            print "[INFO ] create : $path\n";
        }else{
            print "[INFO ] exists : $path\n";
        }
    }
}
?>
