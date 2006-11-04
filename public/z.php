<?php
function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
} 
$time_start = microtime_float();

require_once(dirname(dirname(__FILE__)).'/config/environment.inc.php');
require_once(dirname(dirname(__FILE__)).'/vendor/plugins/zf/config/environment.inc.php');

try{
    require_once 'Zend/Controller/Front.php';
    require_once 'Zend/Controller/Action.php';
    require_once 'Zend/Controller/RewriteRouter.php';
    require_once 'Zend/View.php';
    require_once S2BASE_PHP5_PLUGIN_ZF . '/S2Dispatcher.php';

    $router = new Zend_Controller_RewriteRouter();
    $router->setRewriteBase(S2BASE_PHP5_ZF_ALIAS);
    $fc = Zend_Controller_Front::getInstance();
    $fc->setDispatcher(new S2Dispatcher());
    $fc->setRouter($router);
    Zend_Controller_Front::run(S2BASE_PHP5_ROOT . '/app/modules');

}catch(Exception $e){
    print "<pre><font color=\"red\">{$e->__toString()}</font></pre>\n";
}

$time_end = microtime_float();
$time = $time_end - $time_start;
echo "<br> [ dispatch time : $time seconds ] <br>\n";
?>
