<?php
class @@CLASS_NAME@@ extends PHPUnit2_Framework_TestCase {
    private $module = "@@MODULE_NAME@@";

    function __construct($name) {
        parent::__construct($name);
    }

    function testA() {
        print __METHOD__ . "\n";
    }

    function setUp(){
        print "\n";
    }

    function tearDown() {
    }

}
?>
