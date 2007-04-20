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
 * withSmarty WEBフレームワークのSmartyクラスを継承するコントローラクラス。またビューを兼務します。
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.web.impl
 * @author     klove
 */
class S2Base_SmartyController extends Smarty
    implements S2Base_Controller,S2Base_View {

    const TPL_SUFFIX = S2BASE_PHP5_SMARTY_TPL_SUFFIX;
    public static $config = array();
    protected $request = null;
    protected static $errors = array();
    protected static $rendered = false;
    protected $layout = null;
    protected $actionTpl = null;
    protected $action = null;

    public function __construct(){
        parent::__construct();
        foreach(self::$config as $key=>$val){
            $this->$key = $val;
        }
        if(defined('S2BASE_PHP5_LAYOUT')){
            $this->layout = S2BASE_PHP5_LAYOUT;
        }
    }
    
    /**
     * @see S2Base_Controller::setAction()
     */
    public function setAction(S2Base_Action $action){
        $this->action = $action;
    }

    /**
     * @see S2Base_View::setLayout()
     */
    public function setLayout($layout){
        $this->layout = $layout;
    }

    /**
     * @see S2Base_Controller::setRequest()
     */
    public function setRequest(S2Base_Request $request){
        $this->request = $request;
    }

    /**
     * キーに対するエラー内容を設定します。
     * 
     * @param string $key キー
     * @param mixed $val エラー内容
     */
    public final function putError($key,$val){
        self::$errors[$key] = $val;
    }

    /**
     * キーに設定されているエラー内容を返します。
     * 
     * @param string $key キー
     * @return mixed エラー内容
     */
    public final function getError($key){
        if(isset(self::$errors[$key])){
            return self::$errors[$key];
        }
        return null;
    }

    /**
     * すべてのエラー内容を返します。
     * 
     * @return array 
     */
    public final function getErrors(){
        return self::$errors;
    }

    /**
     * rendered値を設定します。 rendered値は、viewメソッドを実行するかどうかを決定します。
     * 
     * @param boolean $value
     */
    public final function setRendered($value = true){
        self::$rendered = $value;
    }

    /**
     * rendered値を返します。
     * 
     * @return boolean
     */
    public final function isRendered(){
        return self::$rendered;
    }
    
    /**
     * @see S2Base_Controller::process()
     */
    public function process(){
        $this->actionTpl = $this->action->execute($this->request,$this);
        if ($this->actionTpl === null){
            $this->actionTpl = $this->getDefaultActionTpl();
        }
        
        if (!is_string($this->actionTpl)){
            throw new S2Base_RuntimeException('ERR108',array($this->actionTpl));
        }

        if (!$this->isRendered()){
            $this->view();
        }
    }
    
    /**
     * @see S2Base_View::setLayout()
     */
    public function view(){
        $mod = $this->request->getModule();
        $act = $this->request->getAction();
        $this->template_dir = S2BASE_PHP5_ROOT . '/app/modules';
        $this->assign('errors',self::$errors);
        $this->assign('mod_key',S2BASE_PHP5_REQUEST_MODULE_KEY);
        $this->assign('act_key',S2BASE_PHP5_REQUEST_ACTION_KEY);
        $this->assign('module',$mod);
        $this->assign('action',$act);
        $this->assign('request',$this->request);
        $this->assign('module_view_dir',S2BASE_PHP5_ROOT . "/app/modules/$mod/view");
        $this->assign('commons_view_dir',S2BASE_PHP5_ROOT . '/app/commons/view');
        
        if (preg_match("/^redirect:(.+)$/",$this->actionTpl,$matches)){
            $this->redirect($matches[1]);
            return;
        } else if (preg_match("/^file:/",$this->actionTpl)){
            $viewFile = $this->actionTpl;
        } else {
            $viewFile = "$mod/view/" . $this->actionTpl;
            if (!file_exists($this->template_dir . '/' . $viewFile)) {
                throw new S2Base_RuntimeException('ERR109',
                    array($mod, $act, $this->template_dir . '/' . $viewFile));
            }
        }

        if($this->layout == null){
            $this->display($viewFile);
        }else{
            $this->assign('content_for_layout',$viewFile);
            $this->display($this->layout);
        }

        $this->setRendered(true);        
    }

    /**
     * @return string アクション名から導出したテンプレートファイル名
     */
    protected function getDefaultActionTpl(){
        return $this->request->getAction() . self::TPL_SUFFIX;
    }

    /**
     * S2Base_Dispatcherのdispatchメソッドにリクエストをリダイレクトします。
     * 
     * @param string $target リダイレクトターゲット。モジュール名とアクション名をコロンで区切ります。
     *                       コロンが存在しない場合はアクション名として扱います。
     * @throws S2Base_RuntimeException ターゲット文字列のフォーマットに問題があった場合にスローされます。
     */
    private function redirect($target){
        $targets = explode(':',$target);
        if (count($targets) == 2){
            $this->request->setModule($targets[0]);
            $this->request->setAction($targets[1]);
        }else if(count($targets) == 1) {
            $this->request->setAction($targets[0]);
        }else{
            throw new S2Base_RuntimeException('ERR106',array($target));
        }

        S2Base_Dispatcher::dispatch($this->request);
        return;
    }
}
?>
