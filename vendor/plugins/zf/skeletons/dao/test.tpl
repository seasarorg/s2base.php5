<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = '@@MODULE_NAME@@';
    private $controller = '@@CONTROLLER_NAME@@';
    private $container;
    private $dao;
    private $pdo;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testA() {
    }

    public function setUp() {
        print __CLASS__ . '::' . $this->getName() . PHP_EOL;
        require_once(S2BASE_PHP5_ROOT . "/app/modules/{$this->module}/models/{$this->controller}/{$this->controller}.inc.php");
        $this->container = S2ContainerApplicationContext::create();
        $this->dao = $this->container->getComponent("@@DAO_CLASS@@");
        $this->pdo = $this->container->getComponent("dataSource")->getConnection();
        //$this->pdo->beginTransaction();
    }

    public function tearDown() {
        print PHP_EOL;
        //$this->pdo->rollBack();
        $this->pdo = null;
        $this->container = null;
        $this->dao = null;
    }
}
