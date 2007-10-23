<?php
class S2Base_FrontWebController extends sfFrontWebController
{
    public function getAction($moduleName, $actionName)
    {
        $moduleActionClassName = $moduleName . 'Actions';
        $this->prepareApplicationContext($moduleName, $moduleActionClassName);
        $container = S2ContainerApplicationContext::create();
        if ($container->hasComponentDef($moduleActionClassName)) {
            return $container->getComponent($moduleActionClassName);
        } else {
            return parent::getAction($moduleName, $actionName);
        }
    }
    
    protected function prepareApplicationContext($moduleName, $moduleActionClassName)
    {
        if ($this->actionExists($moduleName, $moduleActionClassName)) {
            S2ContainerApplicationContext::$CLASSES[$moduleActionClassName] = 'actions.class.php';
            S2ContainerApplicationContext::import(sfConfig::get('sf_app_module_dir') . "/{$moduleName}/interceptor");
            S2ContainerApplicationContext::import(sfConfig::get('sf_app_module_dir') . "/{$moduleName}/service");
            S2ContainerApplicationContext::import(sfConfig::get('sf_root_dir') . '/config/dao.dicon');
            S2ContainerApplicationContext::import(sfConfig::get('sf_root_dir') . '/lib/dao');
            S2ContainerApplicationContext::import(sfConfig::get('sf_root_dir') . '/lib/entity');
            S2ContainerApplicationContext::registerAspect('/Dao$/', 'dao.interceptor');
        }
    }
    
    
}
