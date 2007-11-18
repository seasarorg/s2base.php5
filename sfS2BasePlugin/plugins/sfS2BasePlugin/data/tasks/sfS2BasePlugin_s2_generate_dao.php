<?php
pake_desc('generate dao with all tables in database');
pake_task('s2_generate_dao', 'project_exists');
pake_alias('s2gendao', 's2_generate_dao');

require_once('S2Container/S2Container.php');
require_once('S2Dao/S2Dao.php');
S2ContainerClassLoader::import(S2CONTAINER_PHP5);
S2ContainerClassLoader::import(S2DAO_PHP5);
simpleAutoloader::registerCallable(array('S2ContainerClassLoader', 'load'));

function run_s2_generate_dao($task, $args) {
    $pluginName = basename(realpath(dirname(__FILE__) . '/../..'));
    //$appName = $args[0];
    //$env = isset($args[1]) ? $args[1] : 'prod';
    $env = isset($args[0]) ? $args[0] : 'prod';

    pake_echo_comment('');
    pake_echo_comment('sfS2BasePlugin s2_gen_dao task');
    pake_echo_comment('');
    pake_echo_comment('Environment    : ' . $env);
    $tableInfo = sfS2BasePlugin_util_getTableInfoFromPdoDicon($env);
    pake_echo_comment('Tables in DB   : ' . implode(', ', array_keys($tableInfo)));
    pake_echo_comment('');

    $dao_dir = sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . 'dao';
    $entity_dir = sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . 'entity';
    $dao_test_dir = sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit';
    $s2_plugin_root_dir = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $pluginName;
    $s2_skeletons_dir = $s2_plugin_root_dir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeletons';

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

