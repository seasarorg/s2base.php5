<?php
require_once('sfInitProject.class.php');
require_once('sfInitApp.class.php');
require_once('sfInitModule.class.php');
class sfCommandFactory
{
    const SEP_CHAR = '-';
    
    public static function create ($cmd)
    {
        $clsName = 'sf';
        $token = strtok($cmd, sfCommandFactory::SEP_CHAR);
        while ($token){
            $clsName .= ucfirst(strtolower($token));
            $token = strtok(sfCommandFactory::SEP_CHAR);
        }
        
        return new $clsName;
    }
}
?>
