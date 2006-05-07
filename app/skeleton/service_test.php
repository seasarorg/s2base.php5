<?php
class @@CLASS_NAME@@ extends PHPUnit2_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";
    private $serviceName = "@@SERVICE_INTERFACE@@";
    private $container;
    private $service;

    function __construct($name) {
        parent::__construct($name);
    }

    function testA() {
        print __METHOD__ . "\n";
    }

    function setUp(){
        print "\n";
        $moduleDir = S2BASE_PHP5_ROOT . "/app/modules/{$this->module}";
        $dicon = $moduleDir . "/dicon/@@SERVICE_INTERFACE@@" . S2BASE_PHP5_DICON_SUFFIX;
        include_once($moduleDir . "/{$this->module}.inc.php");
        $this->container = S2ContainerFactory::create($dicon);
        $this->service = $this->container->getComponent("@@SERVICE_INTERFACE@@");
    }

    function tearDown() {
        $this->container = null;
        $this->service = null;
    }

}
?>
