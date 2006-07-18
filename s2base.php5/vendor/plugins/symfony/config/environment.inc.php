<?php
define('S2BASE_PHP5_SF_ROOT', dirname(dirname(__FILE__)));
define('S2BASE_PHP5_SF_DIR', S2BASE_PHP5_ROOT . S2BASE_PHP5_DS . 'lib' . S2BASE_PHP5_DS . 'sf_sandbox');
define('S2BASE_PHP5_SF_CMD', S2BASE_PHP5_SF_DIR . S2BASE_PHP5_DS . 'bin' . S2BASE_PHP5_DS . 'symfony.php');
define('S2BASE_PHP5_SF_PATH_CACHE', S2BASE_PHP5_VAR_DIR . '/cache/sf.project.cache');
if(file_exists(S2BASE_PHP5_SF_PATH_CACHE)){
    $ini = parse_ini_file(S2BASE_PHP5_SF_PATH_CACHE);
    define('S2BASE_PHP5_SF_DEFAULT_PATH', $ini['projectPath']);
}else{
    define('S2BASE_PHP5_SF_DEFAULT_PATH', S2BASE_PHP5_ROOT . '/s2symfony');
}

define('S2BASE_PHP5_SF_SKELETON_DIR', S2BASE_PHP5_SF_ROOT . S2BASE_PHP5_DS . "skeleton" . S2BASE_PHP5_DS);

define('S2BASE_PHP5_SF_PATH',    true);
define('S2BASE_PHP5_SF_PROJECT', 'project');
define('S2BASE_PHP5_SF_APP',     'app');
define('S2BASE_PHP5_SF_MODULE',  'module');
?>
