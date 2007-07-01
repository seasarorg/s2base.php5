<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2007 the Seasar Foundation and the Others.            |
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
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @package    org.seasar.s2base.zf.controller
 * @author     klove
 */
class S2Base_ZfDispatcherImplTest extends PHPUnit2_Framework_TestCase {
    private $request = null;

    public function __construct($name) {
        parent::__construct($name);
    }

    public function testValidateModule(){
        $moduleName = '';
        $this->request->setModuleName($moduleName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $moduleName = '.';
        $this->request->setModuleName($moduleName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $moduleName = '';
        for ($i=0; $i<=S2Base_ZfDispatcherImpl::PARAM_MAX_LEN ;$i++) {
            $moduleName .= 'a';
        }
        $this->request->setModuleName($moduleName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }
    }

    public function testValidateController(){
        $moduleName = 'default';
        $this->request->setModuleName($moduleName);

        $controllerName = '';
        $this->request->setControllerName($controllerName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $controllerName = '_';
        $this->request->setModuleName($moduleName);
        $this->request->setControllerName($controllerName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $controllerName = '';
        for ($i=0; $i<=S2Base_ZfDispatcherImpl::PARAM_MAX_LEN ;$i++) {
            $controllerName .= 'a';
        }
        $this->request->setControllerName($controllerName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }
    }

    public function testValidateAction(){
        $moduleName = 'default';
        $this->request->setModuleName($moduleName);
        $controllerName = 'index';
        $this->request->setControllerName($controllerName);

        $actionName = '-';
        $this->request->setActionName($actionName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->assertTrue(true);
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->fail();
        }

        $actionName = '.';
        $this->request->setActionName($actionName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->assertTrue(true);
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->fail();
        }

        $actionName = '';
        $this->request->setActionName($actionName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        $actionName = '';
        for ($i=0; $i<=S2Base_ZfDispatcherImpl::PARAM_MAX_LEN ;$i++) {
            $actionName .= 'a';
        }
        $this->request->setActionName($actionName);
        try {
            $this->dispatcher->instantiateController($this->request, '', '');
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

    }

    public function testFormatName() {
        $unformatted = 'aa_bb_cc';
        $isAction = false;
        $ret = $this->dispatcher->formatName($unformatted, $isAction);
        $this->assertEquals($ret, 'Aa_Bb_Cc');

        $unformatted = 'aa_bb_cc';
        $isAction = true;
        $ret = $this->dispatcher->formatName($unformatted, $isAction);
        $this->assertEquals($ret, 'Aabbcc');

        $unformatted = 'aa.bb.cc';
        $isAction = true;
        $ret = $this->dispatcher->formatName($unformatted, $isAction);
        $this->assertEquals($ret, 'AaBbCc');

        $unformatted = 'aa-bb-cc';
        $isAction = true;
        $ret = $this->dispatcher->formatName($unformatted, $isAction);
        $this->assertEquals($ret, 'AaBbCc');

        $unformatted = 'aa-bb-cc';
        $isAction = false;
        $ret = $this->dispatcher->formatName($unformatted, $isAction);
        $this->assertEquals($ret, 'AaBbCc');

        $unformatted = 'aa-bb-cc_dd.ee.ff_gg';
        $isAction = false;
        $ret = $this->dispatcher->formatName($unformatted, $isAction);
        $this->assertEquals($ret, 'AaBbCc_DdEeFf_Gg');
    }

    public function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $this->request = new Zend_Controller_Request_Http();
        $this->dispatcher = new Dispatcher_S2Base_ZfDispatcherImplTest();
    }

    public function tearDown() {
        print "\n";
    }
}

class Dispatcher_S2Base_ZfDispatcherImplTest extends S2Base_ZfDispatcherImpl {
    public function instantiateController(Zend_Controller_Request_Abstract $request, $moduleName, $controllerClassName) {
        return parent::instantiateController($request, $moduleName, $controllerClassName);
    }

    protected function getControllerFromS2Container($request, $moduleName, $controllerClassName){
        return new Controller_S2Base_ZfDispatcherImplTest();
    }
}

class Controller_S2Base_ZfDispatcherImplTest extends Zend_Controller_Action {
    public function __construct(){}
}
?>
