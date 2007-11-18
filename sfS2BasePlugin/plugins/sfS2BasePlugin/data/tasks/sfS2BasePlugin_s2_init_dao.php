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
/*
function sfS2BasePlugin_util_filePutContents($path, $contents, $override = false) {
    if (is_file($path)) {
        if ($override) {
            file_put_contents($path, $contents);
            pake_echo_action('file+', $path);
        }
    } else {
        file_put_contents($path, $contents);
        pake_echo_action('file+', $path);
    }
}

function sfS2BasePlugin_util_getPdoInstance($pdoDicon) {
    $container = S2ContainerFactory::create($pdoDicon);
    $cd = $container->getComponentDef('dataSource');
    $dsn = $cd->getPropertyDef('dsn')->getValue();
    $user = '';
    $pass = '';
    if ($cd->hasPropertyDef('user')) {
        $user = $cd->getPropertyDef('user')->getValue();
    }
    if ($cd->hasPropertyDef('password')) {
        $pass = $cd->getPropertyDef('password')->getValue();
    }
    pake_echo_comment("Dsn            : $dsn");
    return new PDO($dsn, $user, $pass);
}

function sfS2BasePlugin_util_getTableInfoFromPdoDicon($env) {
    $pdoDicon = sfConfig::get('sf_config_dir') . DIRECTORY_SEPARATOR . 'pdo_' . $env . '.dicon';
    pake_echo_comment("Pdo Dicon      : $pdoDicon");
    $pdo = sfS2BasePlugin_util_getPdoInstance($pdoDicon);
    $dbms = S2Dao_DbmsManager::getDbms($pdo);
    $stmt = $pdo->query($dbms->getTableSql());
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $tableInfo = array();
    foreach($tables as $table){
        $tableInfo[$table] = S2Dao_DatabaseMetaDataUtil::getColumns($pdo, $table);
    }
    return $tableInfo;
}

function sfS2BasePlugin_util_camelize($value){
    $value = strtolower($value);
    if (preg_match('/_/', $value)){
        $value = preg_replace('/_/', ' ', $value);
        $value = ucwords($value);
        $matches = array();
        $preSpace = '';
        if (preg_match('/^(\s+)/', $value, $matches)) {
            $preSpace = $matches[1];
        }
        $matches = array();
        $postSpace = '';
        if (preg_match('/(\s+)$/', $value, $matches)) {
             $postSpace = $matches[1];
        }
        $value = preg_replace('/\s/', '', $value);
        $value = preg_replace('/\s/', '_', $preSpace . $value . $postSpace);
        $value = strtolower(substr($value,0,1)) . substr($value,1);
    }
    return $value;
}

function sfS2BasePlugin_util_getAccessorSrc($cols){
    $tempContent  = '    protected $@@PROP_NAME@@;' . PHP_EOL .
                    '    const @@PROP_NAME@@_COLUMN = "@@COL_NAME@@";'  . PHP_EOL .
                    '    public function set@@UC_PROP_NAME@@($val){$this->@@PROP_NAME@@ = $val;}' . PHP_EOL . 
                    '    public function get@@UC_PROP_NAME@@(){return $this->@@PROP_NAME@@;}' . PHP_EOL . PHP_EOL;
    $src = "";
    foreach($cols as $col){
        $prop = sfS2BasePlugin_util_camelize($col);
        $patterns = array("/@@UC_PROP_NAME@@/",
                          "/@@PROP_NAME@@/",
                          "/@@COL_NAME@@/");
        $replacements = array(ucfirst($prop),
                             $prop,
                             $col);
        $src .= preg_replace($patterns,$replacements,$tempContent);
    }
    return $src;
}

function sfS2BasePlugin_util_getToStringSrc($cols){
    $src      = '    public function __toString() {' . PHP_EOL;
    $src     .= '        $buf = array();' . PHP_EOL;
    foreach($cols as $col){
        $prop = sfS2BasePlugin_util_camelize($col);
        $getter = '\' . $this->get' . ucfirst($prop) . '();';
        $src .= '        $buf[] = \'' . "$prop => " . $getter . PHP_EOL;
    }
    $src     .= '        return \'{\' . implode(\', \',$buf) . \'}\';' . PHP_EOL;
    $src     .= '    }' . PHP_EOL;
    return $src;
}

*/