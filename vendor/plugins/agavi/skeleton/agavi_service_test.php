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
        $moduleDir = "@@AG_PROJECT_DIR@@/webapp/modules/{$this->module}";
        $dicon = $moduleDir . "/dicon/@@SERVICE_INTERFACE@@" . S2BASE_PHP5_DICON_SUFFIX;
        include_once('@@AG_PROJECT_DIR@@/tests/modules/@@MODULE_NAME@@/test.inc.php');
        $this->container = S2ContainerFactory::create($dicon);
        $this->service = $this->container->getComponent("@@SERVICE_INTERFACE@@");
    }

    function tearDown() {
        $this->container = null;
        $this->service = null;
    }

}
?>
