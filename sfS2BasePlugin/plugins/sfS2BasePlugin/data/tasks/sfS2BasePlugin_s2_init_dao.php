<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

pake_desc('initialize a new dao with S2Base');
pake_task('s2_init_dao', 'module_exists');
pake_alias('s2dao', 's2_init_dao');

function run_s2_init_dao($task, $args) {
    $pluginName = sfS2BasePluginConfig::PLUGIN_NAME;
    $appName    = $args[0];
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

    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment('sfS2BasePlugin s2_init_dao task');
    sfS2BasePlugin_util_echo_comment('');
    sfS2BasePlugin_util_echo_comment('Environment    : ' . $env);
    sfS2BasePlugin_util_echo_comment("Application    : $appName");
    sfS2BasePlugin_util_echo_comment("Module         : $moduleName");
    sfS2BasePlugin_util_echo_comment("Table Name     : $tableName");
    sfS2BasePlugin_util_echo_comment("Dao Interface  : $daoInterfaceName");
    sfS2BasePlugin_util_echo_comment("Dao Test Class : $daoTestClassName");
    sfS2BasePlugin_util_echo_comment("Entity Class   : $entityClassName");
    $tableInfo = sfS2BasePlugin_util_getTableInfoFromPdoDicon($env);
    sfS2BasePlugin_util_echo_comment('Tables in DB   : ' . implode(', ', array_keys($tableInfo)));
    $columns = array();
    foreach($tableNames as $name) {
        if (!array_key_exists($name, $tableInfo)) {
            throw new Exception("table not found. [$name]");
        } else {
            $columns = array_merge($columns, $tableInfo[$name]);
        }
    }
    $columns = array_unique($columns);
    sfS2BasePlugin_util_echo_comment('Columns        : ' . implode(', ', $columns));
    sfS2BasePlugin_util_echo_comment('');

    $app_dir    = sfConfig::get('sf_app_dir') . $appName;
    $module_dir = $app_dir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName;
    $dao_dir    = $module_dir . DIRECTORY_SEPARATOR . 'dao';
    $entity_dir = $module_dir . DIRECTORY_SEPARATOR . 'entity';
    $unit_test_dir = sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit';
    $dao_test_dir  = $unit_test_dir . DIRECTORY_SEPARATOR . $appName . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR .'dao';
    $s2_plugin_root_dir = sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . $pluginName;
    $s2_skeletons_dir   = $s2_plugin_root_dir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'skeletons' . DIRECTORY_SEPARATOR . 's2_init_dao';

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

    sfS2BasePlugin_util_echo_comment('cache clear.');
    run_clear_cache($task, array($appName));
}
