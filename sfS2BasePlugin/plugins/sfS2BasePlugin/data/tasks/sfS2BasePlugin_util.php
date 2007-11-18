<?php
function sfS2BasePlugin_util_filePutContents($path, $contents, $override = false, $modify = true) {
    if (is_file($path)) {
        if ($override) {
            file_put_contents($path, $contents);
            if ($modify) {
                pake_echo_action('modify', $path);
            } else {
                pake_echo_action('override', $path);
            }
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

