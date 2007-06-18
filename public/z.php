<?php
function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
} 
$time_start = microtime_float();

require_once(dirname(dirname(__FILE__)).'/config/environment.inc.php');
require_once(dirname(dirname(__FILE__)).'/vendor/plugins/zf/config/environment.inc.php');

try{
    Zend_Session::regenerateId();
    S2Base_ZfInitialize::init();
    Zend_Controller_Front::getInstance()->dispatch();
}catch(Exception $e){
    print '<pre><font color="red">' . $e->__toString() . '</font></pre>' . PHP_EOL;
}

$time_end = microtime_float();
$time = $time_end - $time_start;
print "<br> [ dispatch time : $time seconds ] <br>" . PHP_EOL;
?>
