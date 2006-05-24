<?php
function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
} 
$time_start = microtime_float();

require_once(dirname(dirname(__FILE__)).'/config/environment.inc.php');
require_once(dirname(dirname(__FILE__)).'/vendor/pugins/smarty/config/environment.inc.php');
define('S2BASE_PHP5_REQUEST_MODULE_KEY','mod');
define('S2BASE_PHP5_REQUEST_ACTION_KEY','act');
define('S2BASE_PHP5_DEFAULT_MODULE_NAME','Default');
define('S2BASE_PHP5_DEFAULT_ACTION_NAME','index');
try{
    S2Base_Dispatcher::dispatch(new S2Base_RequestImpl());
}catch(Exception $e){
    print "<pre><font color=\"red\">{$e->__toString()}</font></pre>\n";
}

$time_end = microtime_float();
$time = $time_end - $time_start;
echo "<br> [ dispatch time : $time seconds ] <br>\n";
?>