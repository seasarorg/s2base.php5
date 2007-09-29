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
// | Authors: kiyo-shit                                                       |
// +----------------------------------------------------------------------+
//
// $Id:$
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 2.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 2.0.0
 * @package    org.seasar.s2base.zf.controller
 * @author     kiyo-shit
 */
require_once "Zend/View/Helper/Url.php";
abstract class S2Base_ZfControllerTestCase extends PHPUnit_Framework_TestCase
{
    public function __construct($name) {
        parent::__construct($name);
    }
    
    public function get($actionName, $params=array())
    {
        $this->process("GET", $actionName, $params);
    }
    
    public function post($actionName, $params=array())
    {
        $this->process("POST", $actionName, $params);
    }
    
    public function xhr($requestMethod, $actionName, $params=array())
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['HTTP_ACCEPT'] = 'text/javascript, text/html, application/xml, text/xml, */*';
        $this->process($requestMethod, $actionName, $params);
        unset($_SERVER['HTTP_X_REQUESTED_WITH'], $_SERVER['HTTP_ACCEPT']);
    }
    
    protected function process($requestMethod, $actionName, array $params) {
        $_SERVER['REQUEST_METHOD'] = $requestMethod;
        $this->request->__construct(
            'http://test.host/'.$this->moduleName.'/'.$this->controllerName.'/'.$actionName
        );
        $this->request->setParams($params);
        $this->fc
             ->returnResponse(true)
             ->setRequest($this->request)
             ->setResponse($this->response);
        $redirector = 
            Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setExit(false);
        Zend_Controller_Action_HelperBroker::addHelper($redirector);
        $this->fc->dispatch();
    }
    
    public function assigns($name)
    {
        if (defined('S2BASE_PHP5_USE_SMARTY') and S2BASE_PHP5_USE_SMARTY) {
            return $this->controller->view->_tpl_vars[$key];
        } else {
            return $this->controller->view->$name;
        }
        
    }
    
    public function assertRouteFor($expected, $params)
    {
        $router = $this->fc->getRouter();
        $request = $router->route($this->fc->getRequest()->setRequestUri($expected));
        $this->assertEquals($params, $request->getParams());
    }
    
    public function assertResponse($code)
    {
        $this->assertEquals(
            $code,
            $this->response->getHttpResponseCode(),
            "expected <{$code}>, actual is <{$this->response->getHttpResponseCode()}>"
        );
    }
    
    public function assertRedirectedTo($uri)
    {
        $headers = $this->response->getHeaders();
        $this->assertEquals(
            $uri, $headers[0]['value'],
            "expected <{$uri}>, actual is <{$headers[0]['value']}>"
        );
    }

}