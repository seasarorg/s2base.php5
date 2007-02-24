<?php
require_once('Zend/Controller/Dispatcher/Standard.php');
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
                                                   S2Base_ZfDispatcherSupportPlugin::getModuleName($request),
                                                   $className);

        /**
         * Dispatch the method call
         */
        $request->setDispatched(true);
        $controller->dispatch($action);

        // Destroy the page controller instance and reflection objects
        $controller = null;
    }

    public function formatName($unformatted, $isAction = false) {
        return $this->_formatName($unformatted, $isAction);
    }

    protected function instantiateController($request, $moduleName, $controllerClassName) {
        $controller = $this->getControllerFromS2Container($request, $moduleName, $controllerClassName);
        if ($controller == null) {
            $controller = new $controllerClassName($request, $this->getResponse(), $this->getParams());
        }

        if (!$this->isValidController($controller)) {
            throw new Zend_Controller_Dispatcher_Exception("Controller '$controllerClassName' is not an instance of Zend_Controller_Action");
        }
        
        return $controller;
    }

    protected function getControllerFromS2Container($request, $moduleName, $controllerClassName){
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();
        $formatedActionName = $this->getActionMethod($request);
        $actionDicon = S2BASE_PHP5_ROOT 
                     . "/app/modules/$moduleName/$controllerName/dicon/$formatedActionName.dicon";

        $moduleIncFile = S2BASE_PHP5_ROOT 
                     . "/app/modules/$moduleName/$controllerName/$controllerName.inc.php"; 
        $actionIncFile = S2BASE_PHP5_ROOT 
                     . "/app/modules/$moduleName/$controllerName/$actionName.inc.php"; 
        require_once($moduleIncFile);
        if (file_exists($actionIncFile)) {
            require_once($actionIncFile);
        }

        $controller = null;
        if (file_exists($actionDicon)) {
            $container = S2ContainerFactory::create($actionDicon);
            $cd = $container->getComponentDef($controllerClassName);
            if ($cd->getArgDefSize() == 0) {
                $cd->addArgDef(new S2Container_ArgDefImpl($request));
                $cd->addArgDef(new S2Container_ArgDefImpl($this->getResponse()));
                $cd->addArgDef(new S2Container_ArgDefImpl($this->getParams()));
            }
            $controller = $cd->getComponent($controllerClassName);
        }

        return $controller;
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
