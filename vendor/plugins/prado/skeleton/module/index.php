<?php
/** The directory checks may be removed if performance is required **/
$basePath=dirname(__FILE__);
$assetsPath=$basePath."/assets";
$runtimePath=$basePath."/protected/runtime";

if(!is_writable($assetsPath))
	die("Please make sure that the directory $assetsPath is writable by Web server process.");
if(!is_writable($runtimePath))
	die("Please make sure that the directory $runtimePath is writable by Web server process.");

require_once($basePath . '/../@@DOCUMENT_ROOT_S2BASE_RELATION@@' . '/config/environment.inc.php');
require_once($basePath . '/../@@DOCUMENT_ROOT_S2BASE_RELATION@@' . '/vendor/plugins/prado/config/runtime_environment.inc.php');
require_once($basePath . '/../@@DOCUMENT_ROOT_S2BASE_RELATION@@' . '@@S2BASE_MODULE_ENVIRONMENT_INC_PATH@@');

$application=new TApplication();
$application->run();

?>