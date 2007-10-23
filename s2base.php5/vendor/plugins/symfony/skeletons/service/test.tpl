<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $container;
    private $service;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testA(){
    }

    public function setUp(){
        require_once S2BASE_PHP5_ROOT . '/test/unit/TestHelper.php';
        S2ContainerApplicationContext::import(S2BASE_PHP5_ROOT . '/apps/@@APP_NAME@@/modules/@@MODULE_NAME@@/service') ;
        $this->container = S2ContainerApplicationContext::create();
        $this->service = $this->container->getComponent('@@SERVICE_CLASS@@');
    }

    public function tearDown() {
        $this->container = null;
        $this->service = null;
    }
}
