<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = '@@MODULE_NAME@@';
    private $controller = '@@CONTROLLER_NAME@@';
    private $container;
    private $model;
    private $pdo;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testA(){
    }

    public function setUp() {
        print __CLASS__ . '::' . $this->getName() . PHP_EOL;
        S2Base_ZfDb::setDefaultPdoAdapter();
        require_once(S2BASE_PHP5_ROOT . "/app/modules/{$this->module}/models/{$this->controller}/{$this->controller}.inc.php");
        $this->container = S2ContainerApplicationContext::create();
        $this->model = $this->container->getComponent('@@MODEL_CLASS@@');
        $this->pdo = $this->model->getAdapter()->getConnection();
        //$this->pdo->beginTransaction();
    }

    public function tearDown() {
        print PHP_EOL;
        //$this->pdo->rollBack();
        $this->pdo = null;
        $this->container = null;
        $this->model = null;
    }
}
