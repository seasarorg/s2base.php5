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
// $Id: S2Base_ZfRegexValidateFactory.php 286 2007-04-21 04:36:44Z klove $
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.2
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.2
 * @package    org.seasar.s2base.zf.validate.factory.impl
 * @author     klove
 */
abstract class S2Base_ZfAbstractValidateFactory implements S2Base_ZfValidateFactory {

    public static function instantiateDefaultValidator($valClassName, $valKey, Zend_Config $paramConfig) {
        Zend_Loader::loadClass($valClassName);
        $validator = new $valClassName();
        self::setDefaultMessage($validator, $paramConfig->$valKey);
        return $validator;
    }

    public static function setDefaultMessage(Zend_Validate_Interface $instance, Zend_Config $valConfig = null) {
        if ($valConfig === null) {
            return;
        }
        foreach($valConfig as $key => $msg) {
            if (!is_string($msg)) {
                continue;
            }
            $matches = array();
            if (preg_match('/^msg_(.+)/', $key, $matches)) {
                $ref = new ReflectionClass($instance);
                if ($ref->hasConstant($matches[1])) {
                    $instance->setMessage($msg, $ref->getConstant($matches[1]));
                }
            }
        }
    }
}
?>
