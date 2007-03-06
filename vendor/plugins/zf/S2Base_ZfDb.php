<?php
class S2Base_ZfDb {
    private function __construct(){}
    private static $container = null;
    
    public static function factory() {
        if (self::$container === null) {
            self::$container = S2ContainerFactory::create(PDO_DICON);
        }
        $cd = self::$container->getComponentDef('dataSource');
        $username = null;
        $password = null;
        if ($cd->hasPropertyDef('user')) {
            $username = $cd->getPropertyDef('user')->getValue();
        }
        if ($cd->hasPropertyDef('password')) {
            $password = $cd->getPropertyDef('password')->getValue();
        }
        $dsn = $cd->getPropertyDef('dsn')->getValue();
        list($pdoType, $pdoParams)= preg_split('/:/', $dsn, 0);
        $pdoType = 'PDO_' . $pdoType;
        $params = array('username' => $username, 'password' => $password);
        $items = preg_split('/;/', $pdoParams, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($items as $item) {
            list($key, $val)= preg_split('/=/', $item, 0);
            $params[trim($key)] = trim($val);
        }
        return Zend_Db::factory($pdoType, $params);
    }

    public static function setDefaultPdoAdapter() {
        Zend_Db_Table::setDefaultAdapter(self::factory());
    }
}
?>
