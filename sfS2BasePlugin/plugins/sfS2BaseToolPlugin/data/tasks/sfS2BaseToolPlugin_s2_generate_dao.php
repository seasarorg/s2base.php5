<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

pake_desc('generate dao with all tables in database');
pake_task('s2_generate_dao', 'project_exists');
pake_alias('s2gendao', 's2_generate_dao');

function run_s2_generate_dao($task, $args) {
    $pluginName = sfS2BaseToolPluginConfig::PLUGIN_NAME;
    $env = isset($args[0]) ? $args[0] : 'prod';

    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment('sfS2BasePlugin s2_gen_dao task');
    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment('Environment    : ' . $env);
    $tableInfo = sfS2BasePlugin_util_getTableInfoFromPdoDicon($env);
    sfS2BasePlugin_util_echo_comment('Tables in DB   : ' . implode(', ', array_keys($tableInfo)));
    sfS2BasePlugin_util_echo_comment('');

    $dao_dir = sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . 'dao';
    $entity_dir = sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . 'entity';
    $dao_test_dir = sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit';
    $s2_plugin_root_dir = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $pluginName;
    $s2_skeletons_dir = sfS2BasePlugin_util_sfS2BasePluginDir() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeletons' . DIRECTORY_SEPARATOR . 's2_init_dao';

    foreach ($tableInfo as $tableName => $columns) {
        $tableCamelizedName = ucfirst(sfS2BasePlugin_util_camelize($tableName));
        $daoInterfaceName   = $tableCamelizedName . 'Dao';
        $daoTestClassName   = $daoInterfaceName . 'Test';
        $entityClassName    = $tableCamelizedName . 'Entity';

        /** create dao interface file */
        $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'dao.tpl');
        $contents = preg_replace(
                        array('/@@DAO_INTERFACE_NAME@@/',
                              '/@@ENTITY_CLASS_NAME@@/'),
                        array($daoInterfaceName,
                              $entityClassName),
                        $contents);
        $path     = $dao_dir . DIRECTORY_SEPARATOR . $daoInterfaceName . '.class.php';
        sfS2BasePlugin_util_filePutContents($path, $contents);

        /** create entity interface file */
        $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'entity.tpl');
        $contents = preg_replace(
                        array('/@@ENTITY_CLASS_NAME@@/',
                              '/@@TABLE_NAME@@/',
                              '/@@ACCESSOR@@/',
                              '/@@TO_STRING@@/'),
                        array($entityClassName,
                              $tableName,
                              sfS2BasePlugin_util_getAccessorSrc($columns),
                              sfS2BasePlugin_util_getToStringSrc($columns)),
                        $contents);
        $path     = $entity_dir . DIRECTORY_SEPARATOR . $entityClassName . '.class.php';
        sfS2BasePlugin_util_filePutContents($path, $contents);
    }
}

