<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = '@@MODULE_NAME@@';
    private $controller = '@@CONTROLLER_NAME@@';
    private $container;
    private $service;

    public function testA() {
    }

    public function setUp() {
        print PHP_EOL . __CLASS__ . '->' . $this->getName() . '()' . PHP_EOL;
        require_once(S2BASE_PHP5_ROOT . "/app/modules/{$this->module}/models/{$this->controller}/{$this->controller}.inc.php");
        $this->container = S2ContainerApplicationContext::create();
        $this->service = $this->container->getComponent('@@SERVICE_CLASS@@');
    }

    public function tearDown() {
        $this->container = null;
        $this->service = null;
    }
}
