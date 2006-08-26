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
// $Id:$
/**
 * @package org.seasar.s2base.web.impl
 * @author klove
 */
class S2Base_SmartyController extends Smarty
    implements S2Base_Controller,S2Base_View {

    const TPL_SUFFIX = ".tpl";
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
    
    public function setAction(S2Base_Action $action){
        $this->action = $action;
    }

    public function setLayout($layout){
        $this->layout = $layout;
    }

    public function setRequest(S2Base_Request $request){
        $this->request = $request;
    }

    public final function putError($key,$val){
        self::$errors[$key] = $val;
    }

    public final function getError($key){
        if(isset(self::$errors[$key])){
            return self::$errors[$key];
        }
        return null;
    }

    public final function getErrors(){
        return self::$errors;
    }

    public final function setRendered($value){
        self::$rendered = $value;
    }

    public final function isRendered(){
        return self::$rendered;
    }
    
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
    
    public function view(){
        $mod = $this->request->getModule();
        $act = $this->request->getAction();
        $this->template_dir = S2BASE_PHP5_ROOT . "/app/modules";
        $this->assign('errors',self::$errors);
        $this->assign('mod_key',S2BASE_PHP5_REQUEST_MODULE_KEY);
        $this->assign('act_key',S2BASE_PHP5_REQUEST_ACTION_KEY);
        $this->assign('module',$mod);
        $this->assign('action',$act);
        $this->assign('request',$this->request);
        
        if (preg_match("/^redirect:(.+)$/",$this->actionTpl,$matches)){
            $this->redirect($matches[1]);
            return;
        } else if (preg_match("/^file:/",$this->actionTpl)){
            $viewFile = $this->actionTpl;
        } else {
            $viewFile = "$mod/view/" . $this->actionTpl;
            if (!file_exists("{$this->template_dir}/$viewFile")) {
            	throw new S2Base_RuntimeException('ERR109',
                    array($viewFile,$this->template_dir));
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

    protected function getDefaultActionTpl(){
        return $this->request->getAction() . self::TPL_SUFFIX;
    }

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
