<?php
require_once('S2Container/S2Container.php');
require_once('S2Dao/S2Dao.php');
S2ContainerClassLoader::import(S2CONTAINER_PHP5);
S2ContainerClassLoader::import(S2DAO_PHP5);
simpleAutoloader::registerCallable(array('S2ContainerClassLoader', 'load'));

abstract class sfS2BasePluginConfig {
    const PLUGIN_NAME = 'sfS2BasePlugin';
    public static $LOG_LEVEL   = 1;
    public static $LOG_DEBUG   = 0;
}