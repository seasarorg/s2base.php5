<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = '@@MODULE_NAME@@';
    private $container;
    private $dao;
    private $pdo;

    public function testA() {
        try {
            //$this->pdo->beginTransaction();
        } catch(Exception $e) {
            //$this->pdo->rollBack();
        }
        //$this->pdo->rollBack();
    }

    public function setUp() {
        print PHP_EOL . __CLASS__ . '->' . $this->getName() . '()' . PHP_EOL;
        $moduleDir = S2BASE_PHP5_ROOT . "/app/modules/{$this->module}";
        require_once($moduleDir . "/{$this->module}.inc.php");
        $this->container = S2ContainerApplicationContext::create();
        $this->dao = $this->container->getComponent('@@DAO_INTERFACE@@');
        $this->pdo = $this->container->getComponent('dataSource')->getConnection();
    }

    public function tearDown() {
        $this->pdo = null;
        $this->container = null;
        $this->dao = null;
    }

}
