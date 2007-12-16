<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

pake_desc('initialize a new symfony application with S2Base');
pake_task('s2_init_app', 'project_exists');
pake_alias('s2app', 's2_init_app');

function run_s2_init_app($task, $args) {
    /** run default init_app task */
    run_init_app($task, $args);

    /** run init_app task for s2base */
    sfS2BasePlugin_util_echo_comment('initialize a new symfony application with S2Base');
    $pluginName = sfS2BasePluginConfig::PLUGIN_NAME;
    $appName = $args[0];
    $app_dir = sfConfig::get('sf_app_dir') . $appName;
    $s2_plugin_dir = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $pluginName;
    $s2_skeletons_dir = $s2_plugin_dir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeletons' . DIRECTORY_SEPARATOR . 's2_init_app';

    /** craete factory.yml */
    $factoryYml = $app_dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'factories.yml';
    $contents = file_get_contents($factoryYml);
    $template = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'factories.yml');
    sfS2BasePlugin_util_filePutContents($factoryYml, $contents . $template, true, true);

    /** create sfS2BasePlugin_FrontWebController */
    pake_copy($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'sfS2BasePlugin_FrontWebController.tpl',
             $app_dir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'sfS2BasePlugin_FrontWebController.class.php');

    /** craete config/autoload.yml */
    $autoloadYml = sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'autoload.yml';
    if (file_exists($autoloadYml)) {
        $config = sfYaml::load($autoloadYml);
        if (!isset($config['autoload']['s2container'])) {
            $skeleton = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'autoload.yml');
            $skeleton = preg_replace('/^autoload:/', '', $skeleton);
            $contentsYml = file_get_contents($autoloadYml);
            sfS2BasePlugin_util_filePutContents($autoloadYml, $contentsYml . $skeleton, true, true);
        }
    } else {
        pake_copy($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'autoload.yml', $autoloadYml);
    }

    /** create dao.dicon, pdo.dicon */
    pake_copy($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'dao.dicon',
         sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'dao.dicon');
    pake_copy($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'pdo.dicon',
         sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'pdo_prod.dicon');
    pake_copy($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'pdo.dicon',
         sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'pdo_dev.dicon');
    pake_copy($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'pdo.dicon',
         sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'pdo_test.dicon');

    /** create data/s2sample.db */
    $sampleDb = sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 's2base' . DIRECTORY_SEPARATOR . 'sample.db';
    pake_copy($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'sample.db', $sampleDb);
    $sampleSql = sfConfig::get('sf_root_dir') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 's2base' . DIRECTORY_SEPARATOR . 'sample.sql';
    pake_copy($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'sample.sql', $sampleSql);

    /** create test/unit/app directory */
    pake_mkdirs(sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit' . DIRECTORY_SEPARATOR . $appName);
    pake_mkdirs(sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit' . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'modules');

    /** create lib/dao lib/entity directory */
    pake_mkdirs(sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . 'dao');
    pake_mkdirs(sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . 'entity');
}
