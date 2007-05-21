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
// $Id: S2Base_AbstractFilterInterceptor.class.php 278 2007-04-20 11:31:31Z klove $
/**
 * withSmarty WEBフレームワークのフィルタークラス
 * 
 * このabstractクラスを継承してarroundフィルターを実装します。
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.web.impl
 * @author     klove
 */
abstract class S2Base_AbstractFilterInterceptor 
    extends S2Container_AbstractInterceptor
    implements S2Base_FilterInterceptor{

    protected $invocation;
    protected $request;
    protected $moduleName;
    protected $actionName;
    protected $action;
    protected $view;
    protected $controller;

    /**
     * @see S2Container_AbstractInterceptor::invoke()
     */
    public function invoke(S2Container_MethodInvocation $invocation) {
        $this->init($invocation);
        $beforeResult = $this->before();
        if($beforeResult != null){
            return $beforeResult;
        }
        
        return $this->after($invocation->proceed());
    }    
    
    private function init($invocation){
        $this->invocation = $invocation;
        $this->action = $invocation->getThis();
        $methodName = $invocation->getMethod()->getName();

        $this->request = null;
        $this->view = null;
        $this->moduleName = null;
        $this->actionName = null;

        if ($this->action instanceof S2Base_Action and
            $methodName == 'execute'){
            $args = $invocation->getArguments();
            $this->request = $args[0];
            if ($this->request instanceof S2Base_Request){
                $this->moduleName  = $this->request->getModule();
                $this->actionName  = $this->request->getAction();
            }
            $this->view = $args[1];
            $this->controller = $this->view;
        }
    }
}
?>
