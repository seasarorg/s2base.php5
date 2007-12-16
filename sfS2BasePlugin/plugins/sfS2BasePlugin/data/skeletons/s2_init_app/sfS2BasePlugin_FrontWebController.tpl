<?php
class sfS2BasePlugin_FrontWebController extends sfFrontWebController
{
    public function getAction($moduleName, $actionName)
    {
        $action = $this->getActionInternal($actionName . 'Action');
        if ($action !== null) {
            return $action;
        }
        $action = $this->getActionInternal($moduleName . 'Actions');
        if ($action !== null) {
            return $action;
        }
        return parent::getAction($moduleName, $actionName);
    }

    private function getActionInternal($actionClassName) {
        if (isset(S2ContainerApplicationContext::$CLASSES[$actionClassName])) {
            $container = S2ContainerApplicationContext::create();
            if ($container->hasComponentDef($actionClassName)) {
                return $container->getComponent($actionClassName);
            }
        }
        return null;
    }
}
