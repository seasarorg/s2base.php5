<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2006 the Seasar Foundation and the Others.            |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// |                                                                      |
// |     http://www.apache.org/licenses/LICENSE-2.0                       |
// |                                                                      |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,                        |
// | either express or implied. See the License for the specific language |
// | governing permissions and limitations under the License.             |
// +----------------------------------------------------------------------+
// | Authors: klove                                                       |
// +----------------------------------------------------------------------+
//
// $Id:$
/**
 * @author klove
 */
class S2Base_RequestImplTest extends PHPUnit2_Framework_TestCase {
    
    function __construct($name) {
        parent::__construct($name);
    }

    function testGetMpduleDefault() {
        print __METHOD__ . "\n";

        $request = new S2Base_RequestImpl();
        $this->assertEquals($request->getModule(),'Default');
        
    }

    function testGetMpdule() {
        print __METHOD__ . "\n";

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = array(S2BASE_PHP5_REQUEST_MODULE_KEY => "test");
        $request = new S2Base_RequestImpl();
        $this->assertEquals($request->getModule(),'test');
        
    }

    function testGetMpduleException() {
        print __METHOD__ . "\n";

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = array(S2BASE_PHP5_REQUEST_MODULE_KEY => "tes;t");
        try{
            $request = new S2Base_RequestImpl();
            $this->assertTrue(false);
        }catch(Exception $e){
            $this->assertTrue(true);
            print "{$e->getMessage()}\n";
        }
    }

    function testGetModuleMaxLen() {
        print __METHOD__ . "\n";

        $name = "";
        for($i=0;$i<=S2Base_Request::MAX_LEN;$i++){
            $name .= "A";
        }
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(S2BASE_PHP5_REQUEST_MODULE_KEY => $name);
        try{
            $request = new S2Base_RequestImpl();
            $this->assertTrue(false);
        }catch(Exception $e){
            $this->assertTrue(true);
            print "{$e->getMessage()}\n";
        }
    }

    function testGetActionDefault() {
        print __METHOD__ . "\n";

        $request = new S2Base_RequestImpl();
        $this->assertEquals($request->getAction(),'index');
    }

    function testGetAction() {
        print __METHOD__ . "\n";

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(S2BASE_PHP5_REQUEST_ACTION_KEY => "test");
        $request = new S2Base_RequestImpl();
        $this->assertEquals($request->getAction(),'test');
        
    }

    function testGetActionException() {
        print __METHOD__ . "\n";

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(S2BASE_PHP5_REQUEST_ACTION_KEY => "tes;t");
        try{
            $request = new S2Base_RequestImpl();
            $this->assertTrue(false);
        }catch(Exception $e){
            $this->assertTrue(true);
            print "{$e->getMessage()}\n";
        }
    }

    function testGetActionMaxLen() {
        print __METHOD__ . "\n";

        $name = "";
        for($i=0;$i<=S2Base_Request::MAX_LEN;$i++){
            $name .= "A";
        }
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array(S2BASE_PHP5_REQUEST_ACTION_KEY => $name);
        try{
            $request = new S2Base_RequestImpl();
            $this->assertTrue(false);
        }catch(Exception $e){
            $this->assertTrue(true);
            print "{$e->getMessage()}\n";
        }
    }

    function setUp(){
        print "\n";
        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }

    function tearDown() {
    }

}
?>
