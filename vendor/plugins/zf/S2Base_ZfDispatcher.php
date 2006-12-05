<?php
require_once('Zend/Controller/Dispatcher.php');
class S2Base_ZfDispatcher extends Zend_Controller_Dispatcher {
    const ACTION_METHOD = 's2base_action_method';

    public function getControllerName(Zend_Controller_Request_Abstract $request) {
        $controllerName = $request->getControllerName();
        if (empty($controllerName)) {
            $controllerName = $this->getDefaultController();
        }
        return $controllerName;  
    }

    public function getActionName($request) {
        $action = $request->getActionName();
        if (empty($action)) {
            $action = $this->getDefaultAction();
            $request->setActionName($action);
        }
        return $action;
    }    

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
         * Get controller directories
         */
        $directories  = $this->getControllerDirectory();

        /**
         * Get controller class
         */
        $className = $this->_getController($request, $directories);

        /**
         * If no class name returned, report exceptional behaviour
         */
        if (!$className) {
            throw Zend::exception('Zend_Controller_Dispatcher_Exception', '"' . $request->getControllerName() . '" controller does not exist');
        }

        /**
         * Load the controller class file
         */
        Zend::loadClass($className, $directories);

        /**
         * Instantiate controller with request, response, and invocation 
         * arguments; throw exception if it's not an action controller
         */
        /* 
        $controller = new $className($request, $this->getResponse(), $this->getParams());
        if (!$controller instanceof Zend_Controller_Action) {
            throw Zend::exception('Zend_Controller_Dispatcher_Exception', "Controller '$className' is not an instance of Zend_Controller_Action");
        }
        */
        $controller = $this->instantiateController($request,$className);

        /**
         * Retrieve the action name
         */
        $action = $this->_getAction($request);
        $request->setParam(self::ACTION_METHOD, $action);

        /**
         * If method does not exist, default to __call()
         */
        //$doCall = !method_exists($controller, $action);
        $doCall = false;
        
        /**
         * Dispatch the method call
         */
        $request->setDispatched(true);
        $controller->preDispatch();
        if ($request->isDispatched()) {
            // preDispatch() didn't change the action, so we can continue
            if ($doCall) {
                $controller->__call($action, array());
            } else {
                $controller->$action();
            }
            $controller->postDispatch();
        }

        // Destroy the page controller instance and reflection objects
        $controller = null;
    }

    protected function instantiateController($request, $controllerClassName) {
        $controller = $this->getControllerFromS2Container($request, $controllerClassName);
        if ($controller == null) {
            $controller = new $controllerClassName($request, $this->getResponse(), $this->getParams());
        }

        if (!$this->isValidController($controller)) {
            throw Zend::exception('Zend_Controller_Dispatcher_Exception', "Controller '$controllerClassName' is not an instance of Zend_Controller_Action");
        }
        
        return $controller;
    }
    
    
    protected function getControllerFromS2Container($request, $controllerClassName){
        $controllerName = $this->getControllerName($request);
        $actionName = $this->getActionName($request);
        $formatedActionName = $this->_getAction($request);
        $actionDicon = S2BASE_PHP5_ROOT 
                     . "/app/modules/$controllerName/dicon/$formatedActionName.dicon";
        $moduleIncFile = S2BASE_PHP5_ROOT 
                     . "/app/modules/$controllerName/$controllerName.inc.php"; 
        $actionIncFile = S2BASE_PHP5_ROOT 
                     . "/app/modules/$controllerName/$actionName.inc.php"; 
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
