<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright 2005-2007 the Seasar Foundation and the Others.            |
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
 * withSmarty WEBフレームワークのDispatcherクラス
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.web.impl
 * @author     klove
 */
class S2Base_Dispatcher {

    public static $controller = 'S2Base_SmartyController';

    private static $redirects = array();

    /**
     * リクエスト情報からコントローラとアクションを生成し、コントローラのprocessメソッドを実行します。
     * 
     * @param S2Base_Request $request
     */
    public static function dispatch(S2Base_Request $request) {
        self::initialize($request);
        $action = self::instantiateAction($request);

        $controller = self::instantiateController();
        $controller->setAction($action);
        $controller->setRequest($request);
        $controller->process();
    }
    
    /**
     * リクエスト情報より、リダイレクトの登録、モジュールディレクトリの確認、設定ファイルの読み込み初期化処理を行います。
     * 
     * @param S2Base_Request $request
     */
    public static function initialize(S2Base_Request $request){
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

    /**
     * リクエスト情報よりアクションをインスタンス化します。
     * 
     * @param S2Base_Request $request
     */    
    public static function instantiateAction(S2Base_Request $request) {
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

    /**
     * staticプロパティ $controller で設定されたコントローラを生成します。
     * 
     * @return S2Base_Controller
     */
    protected static function instantiateController(){
        $controllerClass = self::$controller;
        return new $controllerClass();
    }

    /**
     * @param string $mod モジュール名
     * @param string $act アクション名
     * @param string $actClassName アクションクラス名
     * @return S2Base_Action
     */
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
