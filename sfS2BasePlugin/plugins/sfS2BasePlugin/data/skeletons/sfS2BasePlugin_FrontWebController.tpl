<?php
class sfS2BasePlugin_FrontWebController extends sfFrontWebController
{
    public function getAction($moduleName, $actionName)
    {
        $moduleActionClassName = $moduleName . 'Actions';
        $actionClassName = $actionName . 'Action';
        $container = S2ContainerApplicationContext::create();
        if ($container->hasComponentDef($actionClassName)) {
            return $container->getComponent($actionClassName);
        } else if ($container->hasComponentDef($moduleActionClassName)) {
            return $container->getComponent($moduleActionClassName);
        } else {
            return parent::getAction($moduleName, $actionName);
        }
    }
}
