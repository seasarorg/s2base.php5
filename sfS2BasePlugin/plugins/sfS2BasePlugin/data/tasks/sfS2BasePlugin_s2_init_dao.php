<?php
pake_desc('initialize a new dao with S2Base');
pake_task('s2_init_dao', 'module_exists');
pake_alias('s2dao', 's2_init_dao');

require_once('S2Container/S2Container.php');
require_once('S2Dao/S2Dao.php');
S2ContainerClassLoader::import(S2CONTAINER_PHP5);
S2ContainerClassLoader::import(S2DAO_PHP5);
simpleAutoloader::registerCallable(array('S2ContainerClassLoader', 'load'));

function run_s2_init_dao($task, $args) {
    $pluginName = basename(realpath(dirname(__FILE__) . '/../..'));
    $appName = $args[0];
    $moduleName = $args[1];

    if (!isset($args[2])) {
        throw new Exception("  table name not found.\n    usage: % symfony s2dao app_name module_name table_name [environment] [dao_class_name]");
    }
    $tableNames = preg_split('/,/', $args[2]);
    $tableName = $tableNames[0];
    $tableCamelizedName = ucfirst(sfS2BasePlugin_util_camelize($tableName));
    $daoInterfaceName = isset($args[4]) ? $args[4] : $tableCamelizedName . 'Dao';
    $daoTestClassName = $daoInterfaceName . 'Test';
    $entityClassName = (isset($args[4]) ? preg_replace('/Dao$/', '', $daoInterfaceName) : $tableCamelizedName) . 'Entity';
    $env = isset($args[3]) ? $args[3] : 'prod';

    pake_echo_comment('');
    pake_echo_comment('sfS2BasePlugin s2_init_dao task');
    pake_echo_comment('');
    pake_echo_comment('Environment    : ' . $env);
    pake_echo_comment("Application    : $appName");
    pake_echo_comment("Module         : $moduleName");
    pake_echo_comment("Table Name     : $tableName");
    pake_echo_comment("Dao Interface  : $daoInterfaceName");
    pake_echo_comment("Dao Test Class : $daoTestClassName");
    pake_echo_comment("Entity Class   : $entityClassName");
    $tableInfo = sfS2BasePlugin_util_getTableInfoFromPdoDicon($env);
    pake_echo_comment('Tables in DB   : ' . implode(', ', array_keys($tableInfo)));
    $columns = array();
    foreach($tableNames as $name) {
        if (!array_key_exists($name, $tableInfo)) {
            throw new Exception("table not found. [$name]");
        } else {
            $columns = array_merge($columns, $tableInfo[$name]);
        }
    }
    $columns = array_unique($columns);
    pake_echo_comment('Columns        : ' . implode(', ', $columns));
    pake_echo_comment('');

    $app_dir = sfConfig::get('sf_app_dir') . $appName;
    $module_dir = $app_dir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
    $dao_dir = $module_dir . DIRECTORY_SEPARATOR . 'dao';
    $entity_dir = $module_dir . DIRECTORY_SEPARATOR . 'entity';
    $unit_test_dir = sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit';
    $dao_test_dir = $unit_test_dir . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR .'dao';
    $s2_plugin_root_dir = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $pluginName;
    $s2_skeletons_dir = $s2_plugin_root_dir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeletons';

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

    /** create dao test class file */
    $contents = file_get_contents($s2_skeletons_dir . DIRECTORY_SEPARATOR . 'dao_test.tpl');
    $contents = preg_replace(
                    array('/@@DAO_INTERFACE_NAME@@/',
                          '/@@DAO_TEST_CLASS_NAME@@/',
                          '/@@APP_NAME@@/',
                          '/@@MODULE_NAME@@/'),
                    array($daoInterfaceName,
                          $daoTestClassName,
                          $appName,
                          $moduleName),
                    $contents);
    $path = $dao_test_dir . DIRECTORY_SEPARATOR . $daoTestClassName . '.class.php';
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

    pake_echo_comment('cache clear.');
    run_clear_cache($task, array($appName));
}
