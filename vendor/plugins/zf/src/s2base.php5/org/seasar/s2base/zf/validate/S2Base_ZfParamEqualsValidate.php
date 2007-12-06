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
class S2Base_ZfParamEqualsValidate extends Zend_Validate_Abstract {

    const NOT_MATCH = 'notMatch';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "'%value%' does not match against request parameter '%param%'"
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'param' => '_param'
    );

    /**
     * Paramimum value
     *
     * @var mixed
     */
    protected $_param = null;

    /**
     * Sets validator options
     *
     * @param  mixed $param
     * @return void
     */
    public function __construct($param)
    {
        $this->setParam($param);
    }

    /**
     * Returns the param option
     *
     * @return mixed
     */
    public function getParam()
    {
        return $this->_param;
    }

    /**
     * Sets the param option
     *
     * @param  mixed $param
     * @return S2Base_ZfValueEqualsValidate Provides a fluent interface
     */
    public function setParam($param)
    {
        $this->_param = $param;
        return $this;
    }

    /**
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if (!$request->has($this->param) or $request->getParam($this->param) !== $value) {
            $this->_error(self::NOT_MATCH);
            return false;
        }
        return true;
    }
}
?>
