<?php
$modDir = dirname(__FILE__);
S2ContainerClassLoader::import($modDir . '/dao');
S2ContainerClassLoader::import($modDir . '/entity');
S2ContainerClassLoader::import($modDir . '/interceptor');
S2ContainerClassLoader::import($modDir . '/service');
S2ContainerClassLoader::import($modDir . '/model');
?>