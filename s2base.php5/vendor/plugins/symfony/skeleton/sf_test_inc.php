<?php
define('SF_ROOT_DIR', '@@SF_ROOT_DIR@@');
define('SF_APP', '@@APP_NAME@@');
$pwd = SF_ROOT_DIR . "/apps/" . SF_APP . "/modules/@@MODULE_NAME@@";
S2ContainerClassLoader::import($pwd . '/dao');
S2ContainerClassLoader::import($pwd . '/entity');
S2ContainerClassLoader::import($pwd . '/interceptor');
S2ContainerClassLoader::import($pwd . '/service');
?>
