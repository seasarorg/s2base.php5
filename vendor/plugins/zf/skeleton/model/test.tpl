<?php
class @@CLASS_NAME@@ extends PHPUnit2_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";
    private $controller = "@@CONTROLLER_NAME@@";
    private $container;
    private $model;

    function __construct($name) {
        parent::__construct($name);
    }

    function testA(){
    }

    function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $controllerDir = S2BASE_PHP5_ROOT . "/app/modules/{$this->module}/{$this->controller}";
        $dicon = $controllerDir . "/dicon/@@MODEL_CLASS@@" . S2BASE_PHP5_DICON_SUFFIX;
        include_once($controllerDir . "/{$this->controller}.inc.php");
        $this->container = S2ContainerFactory::create($dicon);
        $this->model = $this->container->getComponent("@@MODEL_INTERFACE@@");
    }

    function tearDown() {
        print "\n";
        $this->container = null;
        $this->model = null;
    }

}
?>
