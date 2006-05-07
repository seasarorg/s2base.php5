<?php
class MapleCommandUtil {

    public static function getModuleName(){
        $modules = self::getAllModules();
        if(count($modules) == 0){
            throw new Exception("Module not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($modules,"Maple module list");
    }
        
    public static function getAllModules(){
        $entries = scandir(MODULE_DIR);
        $modules = array();
        foreach($entries as $entry) {
            $path = MODULE_DIR . '/' .  $entry;
            if(!preg_match("/^\./",$entry) and is_dir($path)){
                $modules[] = $entry;
            }
        }
        return $modules;
    }

    public static function getActionPath($moduleName){
        $root = MODULE_DIR . "/" . $moduleName;
        $actions = self::searchAction($root);
        if(count($actions) == 0){
            throw new Exception("Action not found at all.");
        }
        return S2Base_StdinManager::getValueFromArray($actions,"Action list");
    }

    private static function searchAction($parent){
        $entries = scandir($parent);
        $ret = array();
        foreach($entries as $entry){
            if(preg_match("/^\./",$entry)){
                continue;
            }
            $path = $parent . "/" . $entry;
            if(is_dir($path)){
                $childRet = self::searchAction($path);
                if(count($childRet) > 0){
                    $ret = array_merge($ret,$childRet);
                }
            }else if(is_readable($path)){
                if(preg_match("/\.class\.php/",$entry)){
                    $ret[] = str_replace(MODULE_DIR,"",$path);
                }
            }
        }
        return $ret;
    }
}
?>
