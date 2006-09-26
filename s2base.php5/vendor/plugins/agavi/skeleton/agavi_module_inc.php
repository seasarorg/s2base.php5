<?php
define('AG_WEBAPP_DIR', '@@AG_WEBAPP_DIR@@');
$pwd = "@@MODULE_DIR@@";
S2ContainerClassLoader::import($pwd . '/dao');
S2ContainerClassLoader::import($pwd . '/entity');
S2ContainerClassLoader::import($pwd . '/interceptor');
S2ContainerClassLoader::import($pwd . '/service');
?>
