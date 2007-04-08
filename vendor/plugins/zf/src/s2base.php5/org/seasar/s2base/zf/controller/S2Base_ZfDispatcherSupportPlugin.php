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
 * @package    org.seasar.s2base.zf.controller
 * @author     klove
 */
class S2Base_ZfDispatcherSupportPlugin extends Zend_Controller_Plugin_Abstract
{
    const PARAM_MAX_LEN = 50;

/*
    public static function getModuleName(Zend_Controller_Request_Abstract $request) {
        return $request->getModuleName();
        $moduleName = $request->getModuleName();
        if ($moduleName === null) {
            return S2BASE_PHP5_ZF_DEFAULT_MODULE;
        }
        return $moduleName;
    }
*/

    public function routeStartup(Zend_Controller_Request_Abstract $request) {
        $moduleDir = S2BASE_PHP5_ROOT . '/app/modules/';
        $modules = scandir($moduleDir);
        foreach ($modules as $module) {
            if (preg_match('/^\./', $module) or
                !is_dir($moduleDir . $module)) {
                continue;
            }
            Zend_Controller_Front::getInstance()->
                               addControllerDirectory($moduleDir . $module, $module);
        }
        
        if (!in_array(S2BASE_PHP5_ZF_DEFAULT_MODULE, $modules)) {
            Zend_Controller_Front::getInstance()->
                               addControllerDirectory($moduleDir, S2BASE_PHP5_ZF_DEFAULT_MODULE);
        }
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        $this->validateModule($request->getModuleName());
        $this->validateController($request->getControllerName());
        $this->validateAction($request->getActionName());
    }

    private function validateModule($value) {
        if (!preg_match('/^[_a-zA-Z0-9]{1,' . self::PARAM_MAX_LEN .'}$/', $value)) {
            throw new S2Base_ZfException("invalid module. [$value]");
        }
    }

    private function validateController($value) {
        if (!preg_match('/^[a-zA-Z0-9]{1,' . self::PARAM_MAX_LEN .'}$/', $value)) {
            throw new S2Base_ZfException("invalid controller. [$value]");
        }
    }

    private function validateAction($value) {
        if (!preg_match('/^[_a-zA-Z0-9\.\-]{1,' . self::PARAM_MAX_LEN .'}$/', $value)) {
            throw new S2Base_ZfException("invalid action. [$value]");
        }
    }
}
?>
