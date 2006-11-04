<?php
require_once('Zend/Controller/Dispatcher.php');
class S2Dispatcher extends Zend_Controller_Dispatcher {
    protected function _dispatch(Zend_Controller_Dispatcher_Token $action, $performDispatch)
    {
        if ($this->_directory === null) {
            throw new Zend_Controller_Dispatcher_Exception('Controller directory never set.  Use setControllerDirectory() first.');
        }

        $className  = $this->formatControllerName($action->getControllerName());

        if (!$performDispatch) {
            return Zend::isReadable($this->_directory . DIRECTORY_SEPARATOR . $className . '.php');
        }

        Zend::loadClass($className, $this->_directory);

        //$controller = new $className();
        //if (!$controller instanceof Zend_Controller_Action) {
        //   throw new Zend_Controller_Dispatcher_Exception("Controller \"$className\" is not an instance of Zend_Controller_Action.");
        //}
        $controller = $this->getController($action->getControllerName(),
                                           $action->getActionName(),
                                           $className);

        $nextAction = $controller->run($this, $action);

        $controller = null;

        return $nextAction;
    }

    protected function getController($controllerName, $actionName,$controllerClassName){
        $formatedActionName = $this->formatActionName($actionName);

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

        if (file_exists($actionDicon)) {
            $container = S2ContainerFactory::create($actionDicon);
            $controller = $container->getComponent($controllerClassName);

            if ($controller instanceof Zend_Controller_Action) {
                return $controller;
            }

            if ($controller instanceof S2Container_DefaultAopProxy and
                $controller->target_ instanceof Zend_Controller_Action) {
                return $controller;
            }

            if (preg_match('/EnhancedByS2AOP$/', get_class($controller)) and
                $controller->target_ instanceof Zend_Controller_Action) {
                return $controller;
            }
        }
        else {
            $controller = new $controllerClassName();
            if ($controller instanceof Zend_Controller_Action) {
                return $controller;
            }
        }

        throw new Zend_Controller_Dispatcher_Exception("Controller \"$controllerClassName\" is not an instance of Zend_Controller_Action.");
    }
}
?>
