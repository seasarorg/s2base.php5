<?php
class @@CLASS_NAME@@ extends PHPUnit2_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";

    function __construct($name) {
        parent::__construct($name);
    }

    function testA() {}

    function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
    }

    function tearDown() {
        print "\n";
    }

}
?>
