<?php
pake_desc('initialize a new symfony module with S2Base');
pake_task('s2_init_module', 'app_exists');
pake_alias('s2module', 's2_init_module');

function run_s2_init_module($task, $args) {
    /** run default init_app task */
    run_init_module($task, $args);

    /** run init_module task for s2base */
    pake_echo_comment('initialize a new symfony module with S2Base');
    $pluginName = basename(realpath(dirname(__FILE__) . '/../..'));
    $appName = $args[0];
    $moduleName = $args[1];
    $app_dir = sfConfig::get('sf_app_dir') . $appName;
    $app_config_dir = $app_dir . DIRECTORY_SEPARATOR . 'config';
    $module_dir = $app_dir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
    $unit_test_dir = sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit';
    $module_unit_test_dir = $unit_test_dir . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;

    $s2_plugin_root_dir = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $pluginName;
    $s2_skeletons_dir   = $s2_plugin_root_dir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeletons';

    /** create service, dao, entity, interceptor directores */
    pake_mkdirs($module_dir . DIRECTORY_SEPARATOR . 'service');
    pake_mkdirs($module_dir . DIRECTORY_SEPARATOR . 'dao');
    pake_mkdirs($module_dir . DIRECTORY_SEPARATOR . 'entity');
    pake_mkdirs($module_unit_test_dir);
    pake_mkdirs($module_unit_test_dir . DIRECTORY_SEPARATOR . 'service');
    pake_mkdirs($module_unit_test_dir . DIRECTORY_SEPARATOR . 'dao');

    /** create module autoload.yml */
    $skeleton = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'module_autoload.yml');
    $skeleton = preg_replace(array('/@@MODULE_NAME@@/'), array($moduleName), $skeleton);
    $autoloadYml = $app_dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'autoload.yml';
    if (file_exists($autoloadYml)) {
        $config = sfYaml::load($autoloadYml);
        if (!isset($config['autoload'][$moduleName . '_service'])) {
            $skeleton = preg_replace('/^autoload:/', '', $skeleton);
            $contentsYml = file_get_contents($autoloadYml);
            sfS2BasePlugin_util_filePutContents($autoloadYml, $contentsYml . $skeleton, true, true);
        }
    } else {
        sfS2BasePlugin_util_filePutContents($autoloadYml, $skeleton);
    }

    /** add S2ContainerApplicationContext setting */
    $skeleton = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'include.tpl');
    $skeleton = preg_replace(
                    array('/@@ACTION_CLASS_NAME@@/', '/@@APP_NAME@@/', '/@@MODULE_NAME@@/'),
                    array($moduleName . 'Actions', $appName, $moduleName),
                    $skeleton);
    $actionIncFile = $module_dir . DIRECTORY_SEPARATOR . 'actions' . DIRECTORY_SEPARATOR . 'actions.inc.php';
    file_put_contents($actionIncFile, $skeleton);
    pake_echo_action('file+', $actionIncFile);

    /** add require_once() setting to actions.inc.php */
    $actions  = file_get_contents($module_dir . DIRECTORY_SEPARATOR . 'actions' . DIRECTORY_SEPARATOR . 'actions.class.php');
    $actions .= PHP_EOL . "require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'actions.inc.php');" . PHP_EOL;
    $actions .= 'S2ContainerApplicationContext::$CLASSES[\'' . $moduleName . 'Actions\'] = \'actions.class.php\';' . PHP_EOL;
    $actionFile = $module_dir . DIRECTORY_SEPARATOR . 'actions' . DIRECTORY_SEPARATOR . 'actions.class.php';
    file_put_contents($actionFile, $actions);
    pake_echo_action('modify', $actionFile);
}
