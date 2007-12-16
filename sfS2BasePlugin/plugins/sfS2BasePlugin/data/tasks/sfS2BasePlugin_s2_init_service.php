<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

pake_desc('initialize a new service with S2Base');
pake_task('s2_init_service', 'module_exists');
pake_alias('s2service', 's2_init_service');

function run_s2_init_service($task, $args) {
    $pluginName = sfS2BasePluginConfig::PLUGIN_NAME;
    $appName    = $args[0];
    $moduleName = $args[1];

    if (!isset($args[2])) {
        throw new Exception("  service class name not found.\n    usage: % symfony s2service app_name module_name service_class_name");
    }
    $serviceClassName     = $args[2];
    $serviceTestClassName = $serviceClassName . 'Test';

    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment('sfS2BasePlugin s2_init_service task');
    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment("Application  : $appName");
    sfS2BasePlugin_util_echo_comment("Module       : $moduleName");
    sfS2BasePlugin_util_echo_comment("Service      : $serviceClassName");
    sfS2BasePlugin_util_echo_comment("Service Test : $serviceTestClassName");
    sfS2BasePlugin_util_echo_comment('');

    $app_dir       = sfConfig::get('sf_app_dir') . $appName;
    $module_dir    = $app_dir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
    $service_dir   = $module_dir . DIRECTORY_SEPARATOR . 'service';
    $unit_test_dir = sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit';
    $service_test_dir   = $unit_test_dir . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR .'service';
    $s2_plugin_root_dir = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $pluginName;
    $s2_skeletons_dir   = $s2_plugin_root_dir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeletons' . DIRECTORY_SEPARATOR . 's2_init_service';

    /** create service class file */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'service.tpl');
    $contents = preg_replace(array('/@@SERVICE_CLASS_NAME@@/'), array($serviceClassName), $contents);
    $path     = $service_dir . DIRECTORY_SEPARATOR . $serviceClassName . '.class.php';
    sfS2BasePlugin_util_filePutContents($path, $contents);

    /** create service test class file */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'service_test.tpl');
    $contents = preg_replace(
                    array('/@@SERVICE_CLASS_NAME@@/',
                          '/@@SERVICE_TEST_CLASS_NAME@@/',
                          '/@@APP_NAME@@/',
                          '/@@MODULE_NAME@@/'),
                    array($serviceClassName,
                          $serviceTestClassName,
                          $appName,
                          $moduleName),
                    $contents);
    $path = $service_test_dir . DIRECTORY_SEPARATOR . $serviceTestClassName . '.class.php';
    sfS2BasePlugin_util_filePutContents($path, $contents);

    sfS2BasePlugin_util_echo_comment('cache clear.');
    run_clear_cache($task, array($appName));
}
