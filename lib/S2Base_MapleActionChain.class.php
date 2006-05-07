<?php
require_once('core/ActionChain.class.php');
class S2Base_MapleActionChain extends ActionChain
{
    function add($name)
    {
        $log =& LogFactory::getLog();
        if ($name == "") {
            $name = DEFAULT_ACTION;
        }
        if (!preg_match("/^[0-9a-zA-Z_]+$/", $name)) {
            $log->info("不正なActionが指定されています(${name})", "ActionChain#add");
            $name = DEFAULT_ACTION;
        }
        list ($className, $filename) = ActionChain::makeNames($name, true);
        if (!$className) {
            $log->info("存在していないActionが指定されています(${name})", "ActionChain#add");
            $name = DEFAULT_ACTION;
            list ($className, $filename) = ActionChain::makeNames($name, true);
        }
        if (isset($this->_list[$name]) && is_object($this->_list[$name])) {
            $log->info("このActionは既に登録されています(${name})", "ActionChain#add");
            return true;
        }
        include_once($filename);
        $action = $this->instantiateAction($name,$className,$filename);
        if (!is_object($action)) {
            $log->error("Actionの生成に失敗しました(${name})", "ActionChain#add");
            return false;
        }
        $this->_list[$name]      =& $action;
        $this->_errorList[$name] =& new ErrorList();
        $this->_position[]       =  $name;
        return true;
    }

    const CLASS_SUFFIX = ".class.php";
    const DICON_SUFFIX = ".dicon";
    public function getDiconPath($classFilePath)
    {
        if(preg_match("/\.class\.php$/",$classFilePath)){
            $diconPath = preg_replace("/". self::CLASS_SUFFIX . "$/",
                                    self::DICON_SUFFIX,
                                    $classFilePath);
            if(is_file($diconPath)){
                return $diconPath;
            }
        }
        return null;
    }

    protected function instantiateAction($actionName,$actionClassName,$actionClassPath)
    {
        $diconPath = $this->getDiconPath($actionClassPath);
        if($diconPath == null){
            return new $actionClassName();
        }

        return $this->_getActionFromS2Container($actionName,$actionClassName,$diconPath);
    }

    private function _getActionFromS2Container($actionName,$actionClassName,$diconPath)
    {
        $container = S2ContainerFileCacheFactory::create($diconPath);
        if($container->hasComponentDef($actionName)){
            $componentKey = $actionName;
        }else if($container->hasComponentDef($actionClassName)){
            $componentKey = $actionClassName;
        }else{
            $componentKey = null;
        }

        if($componentKey != null){
            return $container->getComponent($componentKey);
        }

        $cd = new S2Container_ComponentDefImpl($actionClassName);
        $container->register($cd);
        return $container->getComponent($actionClassName);
    }
}
?>
