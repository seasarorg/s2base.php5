<?php

/*doc
*   classname:  S2Base_SymfonyCommandUtil
*   scope:      PUBLIC
*
*/

class S2Base_SymfonyCommandUtil
{
    private static $appName;
    public static function setAppName($appName)
    {
        self::$appName = $appName;
    }
    
    public static function execSfCmd ($cmd, $args, $pathName)
    {
        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
        );

        $cwd = $pathName;
        $cmd = "symfony $cmd $args";
        $process = proc_open($cmd, $descriptorspec, $pipes, $cwd);
        if (is_resource($process)) {
            fclose($pipes[0]);
            echo stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            proc_close($process);
        }
    }
    
    public static function getAppName(){
        $apps = self::getDirNames(S2BASE_PHP5_ROOT . '/apps');
        if(count($apps) == 0){
            throw new Exception("Application not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($apps,"App list");
    }
    
    public static function getModuleName($appName){
        $modules = self::getDirNames(S2BASE_PHP5_ROOT . '/apps/' . $appName . '/modules');
        if(count($modules) == 0){
            throw new Exception("Module not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,"Module list");
    }    
    
    private function getDirNames($path)
    {
        $ret = array();
        foreach (new DirectoryIterator($path) as $ite) {
            if (!$ite->isDot() && $ite->isDir()) {
                $ret[] = $ite->getFilename();
            }
        }
        return $ret;
    }
}
