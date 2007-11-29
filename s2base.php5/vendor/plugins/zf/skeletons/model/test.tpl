<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = '@@MODULE_NAME@@';
    private $controller = '@@CONTROLLER_NAME@@';
    private $container;
    private $model;
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
        S2Base_ZfDb::setDefaultPdoAdapter();
        require_once(S2BASE_PHP5_ROOT . "/app/modules/{$this->module}/models/{$this->controller}/{$this->controller}.inc.php");
        $this->container = S2ContainerApplicationContext::create();
        $this->model = $this->container->getComponent('@@MODEL_CLASS@@');
        $this->pdo = $this->model->getAdapter()->getConnection();
    }

    public function tearDown() {
        $this->pdo = null;
        $this->container = null;
        $this->model = null;
    }
}
