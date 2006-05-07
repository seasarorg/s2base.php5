<?php
$pwd = dirname(__FILE__);
$packages = array(
    $pwd,
    $pwd . '/action',
    $pwd . '/dao',
    $pwd . '/entity',
    $pwd . '/interceptor',
    $pwd . '/service'
);
ini_set('include_path', 
        implode(PATH_SEPARATOR, $packages) . PATH_SEPARATOR . 
        ini_get('include_path')
);
?>
