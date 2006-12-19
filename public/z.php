<?php
function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
} 
$time_start = microtime_float();

require_once(dirname(dirname(__FILE__)).'/config/environment.inc.php');
require_once(dirname(dirname(__FILE__)).'/vendor/plugins/zf/config/environment.inc.php');
require_once 'Zend/Controller/Front.php';
require_once 'Zend/Controller/Request/Http.php';

//require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Base_ZfDefaultView.php';
//S2Base_ZfDispatcherSupportPlugin::$VIEW_CLASS = 'S2Base_ZfDefaultView';

try{
    $request = new Zend_Controller_Request_Http();
    $request->setBaseUrl();
    $fc = Zend_Controller_Front::getInstance();
    $fc->throwExceptions(true);
    $fc->registerPlugin(new S2Base_ZfDispatcherSupportPlugin());
    $fc->setDispatcher(new S2Base_ZfDispatcher());
    $fc->setRequest($request);
    $fc->setControllerDirectory(S2BASE_PHP5_ROOT . '/app/modules');
    $response = $fc->dispatch();
}catch(Exception $e){
    print '<pre><font color="red">' . $e->__toString() . '</font></pre>' . PHP_EOL;
}

$time_end = microtime_float();
$time = $time_end - $time_start;
echo "<br> [ dispatch time : $time seconds ] <br>" . PHP_EOL;
?>
