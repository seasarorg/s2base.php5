<?php
define('S2BASE_PHP5_AG_ROOT', dirname(dirname(__FILE__)));

define('S2BASE_PHP5_AG_TYPE_PATH',   'path');
define('S2BASE_PHP5_AG_TYPE_MODULE', 'module');
define('S2BASE_PHP5_AG_TYPE_ACTION', 'action');
define('S2BASE_PHP5_AG_TYPE_VIEW',   'view');

define('S2BASE_PHP5_AG_PATH_CACHE', S2BASE_PHP5_VAR_DIR . '/cache/ag.project.cache');
if(file_exists(S2BASE_PHP5_AG_PATH_CACHE)){
    $ini = parse_ini_file(S2BASE_PHP5_AG_PATH_CACHE);
    define('S2BASE_PHP5_AG_DEFAULT_PATH', $ini['projectPath']);
}else{
    define('S2BASE_PHP5_AG_DEFAULT_PATH', S2BASE_PHP5_ROOT);
}
define('S2BASE_PHP5_AG_DEFAULT_MODULE', 'Default');
define('S2BASE_PHP5_AG_DEFAULT_ACTION', 'Index');
define('S2BASE_PHP5_AG_DEFAULT_VIEW',   'Input,Success');

define('S2BASE_PHP5_AG_SKELETON_DIR',      S2BASE_PHP5_AG_ROOT . S2BASE_PHP5_DS . "skeleton" . S2BASE_PHP5_DS);
define('S2BASE_PHP5_AG_SKELETON_CORE_DIR', S2BASE_PHP5_AG_SKELETON_DIR . "core" . S2BASE_PHP5_DS);

define('S2BASE_PHP5_AG_S2AGAVI_DIR', S2BASE_PHP5_DS . "lib" . S2BASE_PHP5_DS . "s2agavi" . S2BASE_PHP5_DS);
define('S2BASE_PHP5_AG_TEST_DIR',    S2BASE_PHP5_DS . "tests" . S2BASE_PHP5_DS . "modules" . S2BASE_PHP5_DS);
define('S2BASE_PHP5_AG_CONFIG_DIR',  S2BASE_PHP5_DS . "config" . S2BASE_PHP5_DS);
define('S2BASE_PHP5_AG_WEBAPP_DIR',  S2BASE_PHP5_DS . "webapp");
define('S2BASE_PHP5_AG_MODULE_DIR',  S2BASE_PHP5_AG_WEBAPP_DIR . S2BASE_PHP5_DS . "modules");
?>
