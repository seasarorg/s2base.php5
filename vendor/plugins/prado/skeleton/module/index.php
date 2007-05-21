<?php
/** 
 * The directory checks may be removed if performance is required 
 */
$assetsPath=$basePath."/assets";
$runtimePath=$basePath."/protected/runtime";
if(!is_writable($assetsPath))
	die("Please make sure that the directory $assetsPath is writable by Web server process.");
if(!is_writable($runtimePath))
	die("Please make sure that the directory $runtimePath is writable by Web server process.");

/**
 * include S2Base and Prado File
 */
$s2BasePath=$basePath . '/../..';
require_once($s2BasePath . '/config/environment.inc.php');
require_once('S2ContainerSplAutoLoad.php');
require_once($s2BasePath . '/lib/prado/framework/prado.php');
require_once($s2BasePath . '/vendor/plugins/prado' . '/S2Base_Prado.class.php');
require_once($s2BasePath . '/vendor/plugins/prado/config/environment.inc.php');
require_once($s2BasePath . '@@S2BASE_MODULE_ENVIRONMENT_INC_PATH@@');

/**
 * Start PRADO Application
 */
$application=new TApplication();
$application->run();


?>