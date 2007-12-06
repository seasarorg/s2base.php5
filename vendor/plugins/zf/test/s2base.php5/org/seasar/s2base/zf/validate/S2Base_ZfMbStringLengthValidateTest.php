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
 * @package    org.seasar.s2base.zf.validate.vactory.impl
 * @author     klove
 */
class S2Base_ZfMbStringLengthValidateTest extends PHPUnit_Framework_TestCase {
    private $validate = null;

    public function testIsValid(){
        $this->assertTrue($this->validate->isValid('あい'));
        $this->validate->setMin(2);
        $this->assertTrue($this->validate->isValid('あ'));
        $this->validate->setEncoding('UTF-8');
        $this->assertFalse($this->validate->isValid('あ'));
        print_r($this->validate->getMessages());
        $this->validate->setMax(3);
        $this->assertTrue($this->validate->isValid('あいう'));
        $this->assertFalse($this->validate->isValid('あいうえ'));
        print_r($this->validate->getMessages());
    }

    public function setUp(){
        print PHP_EOL . __CLASS__ . "::{$this->getName()}\n";
        $this->validate = new S2Base_ZfMbStringLengthValidate();
    }

    public function tearDown() {
    }
}
?>
