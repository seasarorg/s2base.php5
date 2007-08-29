<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";
    private $container;
    private $service;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testA(){
    }

    public function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $moduleDir = S2BASE_PHP5_ROOT . "/app/modules/{$this->module}";
        include_once($moduleDir . "/{$this->module}.inc.php");
        S2ContainerApplicationContext::setIncludePattern('/dao.dicon$/');
        S2ContainerApplicationContext::addIncludePattern('/@@DAO_INTERFACE@@\./');
        S2ContainerApplicationContext::addIncludePattern('/@@SERVICE_CLASS@@\./');
        $this->container = S2ContainerApplicationContext::create();
        $this->service = $this->container->getComponent("@@SERVICE_CLASS@@");
    }

    public function tearDown() {
        print "\n";
        $this->container = null;
        $this->service = null;
    }
}
