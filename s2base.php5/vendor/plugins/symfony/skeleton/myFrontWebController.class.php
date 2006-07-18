<?php

class myFrontWebController extends sfFrontWebController
{
	public function getAction ($moduleName, $actionName)
	{
		$dicon = $this->getDiconPath($moduleName);
		if (is_readable($dicon))
		{
			return $this->_getActionFromS2Container($moduleName, $dicon);
		}
		
		return parent::getAction($moduleName, $actionName);
	}

  public function getDiconPath ($moduleName)
  {
		$diconPath = sfConfig::get('sf_app_module_dir') . '/'
				   . $moduleName . "/dicon/" . $moduleName . ".dicon";
		return $diconPath;
  }

  private function _getActionFromS2Container($moduleName, $diconPath)
  {
  	  $moduleClassName = $moduleName . 'Actions';
      $container = S2ContainerFactory::create($diconPath);
      if($container->hasComponentDef($moduleName)){
          $componentKey = $moduleName;
      }else if($container->hasComponentDef($moduleClassName)){
          $componentKey = $moduleClassName;
      }else{
          $componentKey = null;
      }

      if($componentKey != null){
          return $container->getComponent($componentKey);
      }

      $cd = new S2Container_ComponentDefImpl($moduleClassName, $moduleName);
      $container->register($cd);
      return $container->getComponent($moduleName);
  }	
}
