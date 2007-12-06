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
require_once('Zend/Validate/StringLength.php');
class S2Base_ZfMbStringLengthValidate extends Zend_Validate_StringLength {
    private $encoding = null;

    public function setEncoding($encoding) {
        $this->encoding = $encoding;
        return $this;
    }

    public function getEncoding() {
        return $this->encoding;
    }

    public function isValid($value) {
        $valueString = (string) $value;
        $this->_setValue($valueString);
        if ($this->encoding === null) {
            $length = mb_strlen($valueString);
        } else {
            $length = mb_strlen($valueString, $this->encoding);
        }
        if ($length < $this->_min) {
            $this->_error(self::TOO_SHORT);
        }
        if (null !== $this->_max && $this->_max < $length) {
            $this->_error(self::TOO_LONG);
        }
        if (count($this->_messages)) {
            return false;
        } else {
            return true;
        }
    }
}
?>
