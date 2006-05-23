<?php
define('AG_WEBAPP_DIR', '@@AG_WEBAPP_DIR@@');
$pwd = "@@MODULE_DIR@@";
$packages = array(
    $pwd,
    $pwd . '/dao',
    $pwd . '/service',
    $pwd . '/entity'
);
ini_set('include_path', 
        implode(PATH_SEPARATOR, $packages) . PATH_SEPARATOR . 
        ini_get('include_path')
);
?>
