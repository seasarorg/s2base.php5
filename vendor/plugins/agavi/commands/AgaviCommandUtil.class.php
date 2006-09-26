<?php
class AgaviCommandUtil
{

    public static function getModuleName($targetDir){
        $modules = self::getAllModules($targetDir);
        if(count($modules) == 0){
            throw new Exception("Module not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,"Agavi module list");
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
        case S2BASE_PHP5_AG_TYPE_PATH:
            $msg = 'ProjectFullPath[' .
                   S2BASE_PHP5_AG_DEFAULT_PATH .
                   '] ? : ';
        break;
        case S2BASE_PHP5_AG_TYPE_MODULE:
            $msg = 'ModuleName[' .
                   S2BASE_PHP5_AG_DEFAULT_MODULE .
                   '] ? : ';
        break;
        case S2BASE_PHP5_AG_TYPE_ACTION:
            $msg = 'ActionName[' .
                   S2BASE_PHP5_AG_DEFAULT_ACTION .
                   '] ? : ';
        break;
        case S2BASE_PHP5_AG_TYPE_VIEW:
            $msg = 'ViewName[' .
                   S2BASE_PHP5_AG_DEFAULT_VIEW .
                   '] ? : ';
        break;
        default:
            throw new Exception("Type not found");
        }
        return S2Base_StdinManager::getValue($msg);
    }
    
    public static function validateProjectDir ($webappDir)
    {
        if (!is_dir($webappDir))
        {
            $msg = "Invalid WEBAPP Directory [$webappDir]";
            throw new Exception($msg);
        }
    }
    
    const AG_CMD_PROJECT = 'project';
    const AG_CMD_MODULE  = 'module';
    const AG_CMD_ACTION  = 'action';
    public static function execAgaviCmd ($mode,
                                         $pathName,
                                         $moduleName,
                                         $actionName,
                                         $viewName) {
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
        );
        
        $cwd = $mode == self::AG_CMD_PROJECT ? S2BASE_PHP5_ROOT : $pathName;
        
        $process = proc_open("agavi $mode", $descriptorspec, $pipes, $cwd);
        if (is_resource($process)) {
            if ($mode == self::AG_CMD_PROJECT) {
                fwrite($pipes[0], $pathName . "\n");
            }
            fwrite($pipes[0], $moduleName . "\n");
            fwrite($pipes[0], $actionName . "\n");
            fwrite($pipes[0], $viewName . "\n");
            if ($mode != self::AG_CMD_ACTION) {
                fwrite($pipes[0], "\n");
            }
            fclose($pipes[0]);
            stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            proc_close($process);
        }
    }
    
    public static function createLogicDirectories ($modulePath)
    {
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
    
    public static function createTestDirectory ($projectPath, $moduleName)
    {
        $testDirPath = $projectPath . S2BASE_PHP5_AG_TEST_DIR . $moduleName;
        $dirs = array(
            S2BASE_PHP5_DAO_DIR,
            S2BASE_PHP5_SERVICE_DIR);
        foreach ($dirs as $dir) {
            self::createDirectory($testDirPath . $dir);
        }
    }
    
    public static function createDirectory ($path)
    {
        if(S2Base_CommandUtil::createDirectory($path)){
            print "[INFO ] create : $path\n";
        }else{
            print "[INFO ] exists : $path\n";
        }
    }
    
    public static function writeDiconFile ($incFile, $moduleName, $actionName)
    {
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_AG_SKELETON_DIR .
                                                    'action' .
                                                    S2BASE_PHP5_DICON_SUFFIX); 
        $tempContent = preg_replace("/@@MODULE_NAME@@/",
                                    $moduleName,
                                    $tempContent);
        $tempContent = preg_replace("/@@ACTION_NAME@@/",
                                    $actionName,
                                    $tempContent);
        CmdCommand::writeFile($incFile,$tempContent);
    }
    
    public static function appendSection2AutoloadIni ($webappDir, $moduleName)
    {
        $autoloadIni = $webappDir . '/config/autoload.ini';
        $alSkel = S2Base_CommandUtil::readFile(S2BASE_PHP5_AG_SKELETON_DIR .
                                               'append_autoload.php');
        $alSkel = preg_replace('/@@MODULE_NAME@@/', $moduleName, $alSkel);
        file_put_contents($autoloadIni, $alSkel, FILE_APPEND);
    }
    
    public static function writeModuleIncFile4Test ($pathName, $moduleName)
    {
        $incFile = $pathName .
                   S2BASE_PHP5_AG_TEST_DIR . 
                   $moduleName . 
                   S2BASE_PHP5_DS . 
                   "test.inc.php";
        $tempContent = S2Base_CommandUtil::readFile(S2BASE_PHP5_AG_SKELETON_DIR .
                                                    'agavi_module_inc.php');
        $webappDir = $pathName . S2BASE_PHP5_AG_WEBAPP_DIR;
        $modDir = $pathName .
                  S2BASE_PHP5_AG_MODULE_DIR .
                  S2BASE_PHP5_DS .
                  $moduleName;
        $patterns = array("/@@MODULE_DIR@@/","/@@AG_WEBAPP_DIR@@/");
        $replacements = array($modDir, $webappDir);
        $tempContent = preg_replace($patterns,$replacements,$tempContent);
        CmdCommand::writeFile($incFile,$tempContent);
    }
}
?>
