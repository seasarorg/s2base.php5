<?php
/** The directory checks may be removed if performance is required **/
$basePath=dirname(__FILE__);
$assetsPath=$basePath."/assets";
$runtimePath=$basePath."/protected/runtime";

if(!is_writable($assetsPath))
	die("Please make sure that the directory $assetsPath is writable by Web server process.");
if(!is_writable($runtimePath))
	die("Please make sure that the directory $runtimePath is writable by Web server process.");

require_once('@@S2BASE_ENVIRONMENT_INC_PATH@@');
require_once('@@PRADO_RUNTIME_ENVIRONMENT_INC_PATH@@');
require_once('@@S2BASE_MODULE_ENVIRONMENT_INC_PATH@@');

$application=new TApplication();
$application->run();

?>