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
 * @version    Release: 1.0.2
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.2
 * @package    org.seasar.s2base.zf.controller
 * @author     klove
 */
class S2Base_ZfAccessControllPlugin extends Zend_Controller_Plugin_Abstract
{
    const DEFAULT_KEY  = 'default';

    private $configFile = null;
    private $config = null;
    private $handlers = array();

    public function __construct() {
        $this->configFile = S2BASE_PHP5_ROOT . '/config/access.ini';
        $this->handlers['allow'] = new S2Base_ZfAccessControllAllowHandler();
        $this->handlers['deny']  = new S2Base_ZfAccessControllDenyHandler();
        $this->handlers['ana']   = new S2Base_ZfAccessControllAnAHandler();
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        if (!$this->config instanceof Zend_Config_Ini) {
            $this->config = new Zend_Config_Ini($this->configFile, null);
        }
        
        while ($this->config->valid()) {
            $paramName = strtolower($this->config->key());
            if ($paramName === self::DEFAULT_KEY) {
                $this->config->next();
                continue;
            }
            $paramConfig = $this->config->current();
            if (!isset($paramConfig->module) or 
                !isset($paramConfig->controller) or
                !isset($paramConfig->action) or 
                !isset($paramConfig->access)) {
                throw new Exception('invalid access config');
            }


            if (preg_match($config->module, $request->getModuleName()) == 1 and
                preg_match($config->module, $request->getControllerName()) == 1 and
                preg_match($config->module, $request->getActionName()) == 1) {
                if (isset($this->handlers[strtolower($paramConfig->access)])) {
                    $this->handlers[$paramConfig->access]->handle($request, $paramConfig);
                } else {
                    throw new Exception('handler not found');
                }
            }
            $this->config->next();
        }
    }
}

interface S2Base_ZfAccessControllHandler {
    public function handle(Zend_Controller_Request_Abstract $request, Zend_Config_Ini $config);
}

class S2Base_ZfAccessControllAllowHandler implements S2Base_ZfAccessControllHandler {
    public function handle(Zend_Controller_Request_Abstract $request, Zend_Config_Ini $config){
        if (isset($config->module) and preg_match($config->module, $request->getModuleName()) == 1) {
            if (isset($config->controller)){
                if (preg_match($config->controller, $request->getControllerName()) == 1) {
                    if (isset($config->action) and preg_match($config->action, $request->getActionName()) == 1) {
                    } else {
                    }
                }
            } else {
            }
        }
    }
}
?>
