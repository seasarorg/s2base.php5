<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2006 the Seasar Foundation and the Others.            |
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
// $Id$
/**
 * @package org.seasar.s2base.web.impl
 * @author klove
 */
class S2Base_Dispatcher {

    public static $controller = 'S2Base_SmartyController';

    private static $redirects = array();

    public static function dispatch($request) {
        self::initialize($request);
        $action = self::instantiateAction($request);

        $controller = self::instantiateController();
        $controller->setAction($action);
        $controller->setRequest($request);
        $controller->process();
    }
    
    public static function initialize($request){
        $mod = $request->getModule();
        $act = $request->getAction();
        self::pushRedirect($mod . ":" . $act);
        $actClassName = ucfirst($act) . "Action";

        if(!is_dir(S2BASE_PHP5_ROOT . "/app/modules/$mod")){
            throw new S2Base_RuntimeException('ERR103',array($mod));
        }

        self::requireIfExists(S2BASE_PHP5_ROOT . "/app/modules/$mod/$mod.inc.php");
        self::requireIfExists(S2BASE_PHP5_ROOT . "/app/modules/$mod/action/$actClassName.inc.php");
    }
    
    public static function instantiateAction($request) {
        $mod = $request->getModule();
        $act = $request->getAction();
        $actClassName = ucfirst($act) . "Action";
        $actClassFile = S2BASE_PHP5_ROOT . 
            "/app/modules/$mod/action/$actClassName.class.php";

        if(!is_readable($actClassFile)){
           return new S2Base_SimpleAction();
        }

        require_once($actClassFile);
        return self::getActionWithS2Container($mod,$act,$actClassName);
    }

    protected static function instantiateController(){
        $controllerClass = self::$controller;
        return new $controllerClass();
    }

    private static function getActionWithS2Container($mod,$act,$actClassName){
        $dicon = S2BASE_PHP5_ROOT . 
                 "/app/modules/$mod/dicon/$actClassName" .
                 S2BASE_PHP5_DICON_SUFFIX;

        if(!is_readable($dicon)){
            return new $actClassName();
        }

        $container = S2ContainerFactory::create($dicon);
        $container->init();
        $componentKey = null;
        if ($container->hasComponentDef($act)){
            $componentKey = $act;
        }else if ($container->hasComponentDef($actClassName)){
            $componentKey = $actClassName;
        }
        
        if($componentKey != null){
            return $container->getComponent($componentKey);
        }
        
        $cd = new S2Container_ComponentDefImpl($actClassName);
        $container->register($cd);
        return $cd->getComponent();
    }

    private static function requireIfExists($file){
        if(is_readable($file)){
            require_once($file);
        }
    }

    private static function pushRedirect($target){
        if (in_array($target,self::$redirects)){
            throw new S2Base_RuntimeException('ERR107',array($target,self::$redirects));
        }else{
            self::$redirects[] = $target;
        }
    }
}
?>
