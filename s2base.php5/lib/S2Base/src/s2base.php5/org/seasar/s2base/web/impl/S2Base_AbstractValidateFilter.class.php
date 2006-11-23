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
// $Id$
/**
 * @package org.seasar.s2base.web.impl
 * @author klove
 */
abstract class S2Base_AbstractValidateFilter 
    extends S2Base_AbstractBeforeFilter {

    protected $rule;
    
    abstract public function validate();

    abstract public function getSuffix();

    /**
     * @see S2Base_FilterInterceptor::before()
     */
    public function before(){
        if($this->rule == null){
            $ruleFile = S2BASE_PHP5_ROOT . 
                    "/app/modules/" . 
                    $this->moduleName . "/validate/".
                    $this->actionName . "." .
                    $this->getSuffix() . ".ini";
            if(is_readable($ruleFile)){
                $this->rule = parse_ini_file($ruleFile,true);
            }
        }
        return $this->validate();   
    }
}
?>