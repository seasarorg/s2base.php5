<?php
class S2Base_AgaviController extends FrontWebController {
    
	public function getAction ($moduleName, $actionName)
	{
		$file = AG_MODULE_DIR . '/' . $moduleName . '/actions/' . $actionName . 'Action.class.php';
		if (file_exists($file)) {
			require_once($file);
		}
        
		$longActionName = $actionName;

		// Nested action check?
		$position = strrpos($actionName, '/');
		if ($position > -1) {
			$longActionName = str_replace('/', '_', $actionName);
			$actionName = substr($actionName, $position + 1);
		}

		if (class_exists($moduleName . '_' . $longActionName . 'Action', false)) {
			$class = $moduleName . '_' . $longActionName . 'Action';
		} elseif (class_exists($moduleName . '_' . $actionName . 'Action', false)) {
			$class = $moduleName . '_' . $actionName . 'Action';
		} elseif (class_exists($longActionName . 'Action', false)) {
			$class = $longActionName . 'Action';
		} else {
			$class = $actionName . 'Action';
		}
        
        $diconPath = self::getDiconPath($moduleName, $longActionName);
        if (is_readable($diconPath))
        {
            return $this->_getActionFromS2Container($class, $diconPath);
        }
        
		return new $class();
	}
    
    public static function getDiconPath ($moduleName, $longActionName)
    {
		return AG_MODULE_DIR . '/' . $moduleName . '/dicon/' . $longActionName . '.dicon';
    }
    
    private function _getActionFromS2Container($class, $diconPath)
    {
        $modActName = str_replace('Action', '', $class);
        @list($moduleName, $actionName) = explode('_', $modActName);
        $container = S2ContainerFactory::create($diconPath);
        if ($container->hasComponentDef($actionName)) // actName
        {
            $componentKey = $actionName;
        }
        else if ($container->hasComponentDef($modActName)) // modName_actName or actName(M3)
        {
            $componentKey = $modActName;
        }
        else if ($container->hasComponentDef($class)) // className
        {
            $componentKey = $class;
        }
        else
        {
            $componentKey = null;
        }

        if ($componentKey != null)
        {
            return $container->getComponent($componentKey);
        }
        $cd = new S2Container_ComponentDefImpl($class, $actionName);
        $container->register($cd);
        return $container->getComponent($actionName);
    }
}
?>