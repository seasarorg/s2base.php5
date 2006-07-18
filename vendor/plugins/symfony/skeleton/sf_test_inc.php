<?php
define('SF_ROOT_DIR', '@@SF_ROOT_DIR@@');
define('SF_APP', '@@APP_NAME@@');
$pwd = SF_ROOT_DIR . "/apps/" . SF_APP . "/modules/@@MODULE_NAME@@";
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
