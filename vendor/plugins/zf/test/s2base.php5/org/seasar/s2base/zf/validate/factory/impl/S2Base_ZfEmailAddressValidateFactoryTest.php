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
// $Id: S2Base_ZfRegexValidateFactoryTest.php 287 2007-04-21 04:37:46Z klove $
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @package    org.seasar.s2base.zf.validate.vactory.impl
 * @author     klove
 */
class S2Base_ZfEmailAddressValidateFactoryTest extends PHPUnit_Framework_TestCase {
    public function __construct($name) {
        parent::__construct($name);
    }

    public function testId(){
        $this->assertEquals($this->factory->getId(), 'email');
    }

    public function testGetInstance(){
        $config = new Zend_Config(array(), true);
        $valKey = $this->factory->getId();
        
        try {
            $validator1 = $this->factory->getInstance('aa', $config);
            $validator2 = $this->factory->getInstance('aa', $config);
            $this->assertReference($validator1, $validator2);
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->fail();
        }
/*
        try {
            $config->$valKey = array('min' => 1);
            $this->factory->getInstance('aa', $config);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        try {
            $config->$valKey = array('max' => 10);
            $this->factory->getInstance('aa', $config);
            $this->fail();
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->assertTrue(true);
        }

        try {
            $config->$valKey = array('min' => 1, 'max' => 10);
            $validater = $this->factory->getInstance('aa', $config);
            $this->assertTrue($validater instanceof Zend_Validate_Between);
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->fail();
        }
*/
    }

/*
    public function testValidate(){
        $config = new Zend_Config(array(), true);
        $valKey = $this->factory->getId();
        try {
            $config->$valKey = array('min' => 1, 'max' => 10);
            $validater = $this->factory->getInstance('aa', $config);
            $this->assertFalse($validater->isValid(0));
            $this->assertTrue ($validater->isValid(1));
            $this->assertTrue ($validater->isValid(2));
            $this->assertTrue ($validater->isValid(9));
            $this->assertTrue ($validater->isValid(10));
            $this->assertFalse($validater->isValid(11));
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->fail();
        }
    }

    public function testValidateInclusive(){
        $config = new Zend_Config(array(), true);
        $valKey = $this->factory->getId();
        try {
            $config->$valKey = array('min' => 1, 'max' => 10, 'inclusive' => false);
            $validater = $this->factory->getInstance('aa', $config);
            $this->assertFalse($validater->isValid(0));
            $this->assertFalse($validater->isValid(1));
            $this->assertTrue ($validater->isValid(2));
            $this->assertTrue ($validater->isValid(9));
            $this->assertFalse($validater->isValid(10));
            $this->assertFalse($validater->isValid(11));
        } catch(S2Base_ZfException $e) {
            print $e->getMessage() . PHP_EOL;
            $this->fail();
        }
    }
*/
    public function setUp(){
        print __CLASS__ . "::{$this->getName()}\n";
        $this->factory = new S2Base_ZfEmailAddressValidateFactory();
    }

    public function tearDown() {
        print "\n";
    }
}
?>
