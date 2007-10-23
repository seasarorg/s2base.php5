<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $container;
    private $dao;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testA() {
    }

    public function setUp(){
        require_once S2BASE_PHP5_ROOT . '/test/unit/TestHelper.php';
        $this->container = S2ContainerApplicationContext::create();
        $this->dao = $this->container->getComponent("@@DAO_CLASS@@");
    }

    public function tearDown() {
        $this->container = null;
        $this->dao = null;
    }
}
