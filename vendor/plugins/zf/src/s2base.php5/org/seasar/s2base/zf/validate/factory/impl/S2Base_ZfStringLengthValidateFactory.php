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
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2006 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.2
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.2
 * @package    org.seasar.s2base.zf.validate.factory.impl
 * @author     klove
 */
class S2Base_ZfStringLengthValidateFactory implements S2Base_ZfValidateFactory {
    const ID = 'strlen';
    private $instance = null;
    private $validateClassName = 'Zend_Validate_StringLength';

    public function getId() {
        return self::ID;
    }

    public function getInstance($paramName, Zend_Config $config) {
        $valKey = self::ID;
        $min = 0;
        $max = null;
        if ($config->$valKey !== null and $config->$valKey->min !== null) {
            $min = $config->$valKey->min;
        }
        if ($config->$valKey !== null and $config->$valKey->man !== null) {
            $man = $config->$valKey->man;
        }

        if ($this->instance === null) {
            Zend_Loader::loadClass($this->validateClassName);
            $this->instance = new $this->validateClassName($config->$valKey->min,
                                                           $config->$valKey->max);
        } else {
            $this->instance->setMin($config->$valKey->min);
            $this->instance->setMax($config->$valKey->max);
        }
        return $this->instance;
    }
}
?>
