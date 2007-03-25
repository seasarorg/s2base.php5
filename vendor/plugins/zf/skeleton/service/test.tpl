<?php
class @@CLASS_NAME@@ extends PHPUnit_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";
    private $controller = "@@CONTROLLER_NAME@@";
    private $container;
    private $service;

    function __construct($name) {
        parent::__construct($name);
    }

    function testA(){
    }

    function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $controllerDir = S2BASE_PHP5_ROOT . "/app/modules/{$this->module}/{$this->controller}";
        $dicon = $controllerDir . "/dicon/@@SERVICE_CLASS@@" . S2BASE_PHP5_DICON_SUFFIX;
        include_once($controllerDir . "/{$this->controller}.inc.php");
        $this->container = S2ContainerFactory::create($dicon);
        $this->service = $this->container->getComponent("@@SERVICE_INTERFACE@@");
    }

    function tearDown() {
        print "\n";
        $this->container = null;
        $this->service = null;
    }

}
?>
