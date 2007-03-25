<?php
class @@CLASS_NAME@@ extends PHPUnit2_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";
    private $container;
    private $dao;

    function __construct($name) {
        parent::__construct($name);
    }

    function testA() {
        print __METHOD__ . "\n";
    }

    function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $moduleDir = "@@SF_ROOT_DIR@@/apps/@@APP_NAME@@/modules/{$this->module}";
        $dicon = $moduleDir . "/dicon/@@DAO_CLASS@@" . S2BASE_PHP5_DICON_SUFFIX;
        include_once('@@SF_ROOT_DIR@@/test/@@APP_NAME@@/@@MODULE_NAME@@/test.inc.php');
        $this->container = S2ContainerFactory::create($dicon);
        $this->dao = $this->container->getComponent("@@DAO_CLASS@@");
    }

    function tearDown() {
        $this->container = null;
        $this->dao = null;
    }

}
?>