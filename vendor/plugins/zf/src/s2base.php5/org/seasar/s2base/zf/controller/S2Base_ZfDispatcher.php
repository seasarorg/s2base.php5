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
class S2Base_ZfDispatcher extends Zend_Controller_Dispatcher_Standard {

    /**
     * Dispatch to a controller/action
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @return boolean
     */
    public function dispatch(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response)
    {
        $this->setResponse($response);

        /**
         * Get controller class
         */
        if (!$this->isDispatchable($request)) {
            if (!$this->getParam('useDefaultControllerAlways')) {
                require_once 'Zend/Controller/Dispatcher/Exception.php';
                throw new Zend_Controller_Dispatcher_Exception('Invalid controller specified (' . $request->getControllerName() . ')');
            }

            $className = $this->getDefaultControllerClass($request);
        } else {
            $className = $this->getControllerClass($request);
            if (!$className) {
                $className = $this->getDefaultControllerClass($request);
            }
        }

        /**
         * Load the controller class file
         */
        $className = $this->loadClass($className);

        $action = $this->getActionMethod($request);
        $controller = $this->instantiateController($request,
                                                   $request->getModuleName(),
                                                   $className);

        /**
         * Dispatch the method call
         */
        $request->setDispatched(true);
        $disableOb = $this->getParam('disableOutputBuffering');
        if (empty($disableOb)) {
            ob_start();
        }
        $controller->dispatch($action);
        if (empty($disableOb)) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }

        // Destroy the page controller instance and reflection objects
        $controller = null;
    }

    public function formatName($unformatted, $isAction = false) {
        return $this->_formatName($unformatted, $isAction);
    }

    protected function instantiateController($request, $moduleName, $controllerClassName) {
        $controller = $this->getControllerFromS2Container($request, $moduleName, $controllerClassName);

        if (!$this->isValidController($controller)) {
            throw new Zend_Controller_Dispatcher_Exception("Controller '$controllerClassName' is not an instance of Zend_Controller_Action");
        }
        
        return $controller;
    }

    protected function getControllerFromS2Container($request, $moduleName, $controllerClassName){
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();
        $formatedActionName = $this->formatName($actionName);
        $actionMethodName = $this->getActionMethod($request);
        $controllerDir = "/app/modules/$moduleName/$controllerName";
        $actionDicon   = S2BASE_PHP5_ROOT . $controllerDir . "/dicon/$actionMethodName.dicon";
        $moduleIncFile = S2BASE_PHP5_ROOT . $controllerDir . "/$controllerName.inc.php";
        $actionIncFile = S2BASE_PHP5_ROOT . $controllerDir . "/$actionMethodName.inc.php";

        require_once($moduleIncFile);
        if (is_file($actionIncFile)) {
            require_once($actionIncFile);
        }

        $container = null;
        $cd = null;
        if (is_file($actionDicon)) {
            $container = S2ContainerFactory::create($actionDicon);
            $cd = $container->getComponentDef($controllerClassName);
        } else {
            $container = new S2ContainerImpl();
            $container->includeChild(S2ContainerFactory::create(S2BASE_PHP5_ZF_APP_DICON));
            $cd = new S2Container_ComponentDefImpl($controllerClassName);
            $container->register($cd);
        }

        if ($cd->getArgDefSize() == 0) {
            $cd->addArgDef(new S2Container_ArgDefImpl($request));
            $cd->addArgDef(new S2Container_ArgDefImpl($this->getResponse()));
            $cd->addArgDef(new S2Container_ArgDefImpl($this->getParams()));
        }

        return $container->getComponent($controllerClassName);
    }

    private function isValidController($controller){
        if ($controller instanceof Zend_Controller_Action) {
            return true;
        }

        if ($controller instanceof S2Container_DefaultAopProxy and
            $controller->target_ instanceof Zend_Controller_Action) {
            return true;
        }

        if (preg_match('/'. S2Container_AopProxyGenerator::CLASS_NAME_POSTFIX . '$/', get_class($controller)) and
            $controller->target_ instanceof Zend_Controller_Action) {
            return true;
        }
        return false;
    }
}
?>
