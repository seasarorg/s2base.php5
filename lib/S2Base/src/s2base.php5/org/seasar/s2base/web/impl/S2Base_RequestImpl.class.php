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
 * withSmarty WEBフレームワークのリクエストクラス
 * 
 * @copyright  2005-2006 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.0
 * @package    org.seasar.s2base.web.impl
 * @author     klove
 */
class S2Base_RequestImpl implements S2Base_Request {

    protected $request = array();
    protected $module;
    protected $action;
    
    public function __construct() {
        //$this->request = $_REQUEST;
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->request = $_POST;
        } else {
            $this->request = $_GET;
        }
        $this->setModule();
        $this->setAction();
    }

    /**
     * @see S2Base_Request::getModule()
     */
    public function getModule(){
        return $this->module;
    }
    
    /**
     * @see S2Base_Request::getAction()
     */
    public function getAction(){
        return $this->action;
    }
    
    /**
     * @see S2Base_Request::getParam()
     */    
    public function getParam($key){
        if(isset($this->request[$key])){
            return $this->request[$key];
        }
        return null;
    }
    
    /**
     * @see S2Base_Request::setParam()
     */    
    public function setParam($key,$val){
        $this->request[$key] = $val;
    }

    /**
     * @return array all request param
     */
    public function getParams(){
        return $this->request;
    }

    /**
     * @see S2Base_Request::hasParam()
     */    
    public function hasParam($key){
        return isset($this->request[$key]);
    }
    
    /**
     * モジュール名を設定します。
     * デフォルトモジュール名を S2BASE_PHP5_DEFAULT_MODULE_NAME 定数で指定できます。
     * 
     * @param string $module モジュール名
     * @throws S2Base_RuntimeException モジュール名の検証に失敗したときにスローされます。
     */    
    public function setModule($module = null){
        if ($module == null){
            $this->module = $this->getParam(S2BASE_PHP5_REQUEST_MODULE_KEY);
            if($this->module == null){
                $this->module = S2BASE_PHP5_DEFAULT_MODULE_NAME;
            }
        }else{
            $this->module = $module; 
            $this->setParam(S2BASE_PHP5_REQUEST_MODULE_KEY,$module);
        }

        if(!$this->isValidName($this->module)){
            throw new S2Base_RuntimeException('ERR102',
                                           array(S2BASE_PHP5_REQUEST_MODULE_KEY,
                                                 $this->module));
        }
    }

    /**
     * アクション名を設定します。
     * デフォルトアクション名を S2BASE_PHP5_DEFAULT_ACTION_NAME 定数で指定できます。
     * 
     * @param string $action アクション名
     * @throws S2Base_RuntimeException アクション名の検証に失敗したときにスローされます。
     */    
    public function setAction($action = null){
        if ($action == null){
            $this->action = $this->getParam(S2BASE_PHP5_REQUEST_ACTION_KEY);
            if($this->action == null){
                $this->action = S2BASE_PHP5_DEFAULT_ACTION_NAME;
            }
        }else{
            $this->action = $action;
            $this->setParam(S2BASE_PHP5_REQUEST_ACTION_KEY,$action);
        }

        if(!$this->isValidName($this->action)){
            throw new S2Base_RuntimeException('ERR102',
                                           array(S2BASE_PHP5_REQUEST_ACTION_KEY,
                                                 $this->action));
        }
    }

    protected function isValidName($name){
        if(!preg_match("/^\w+$/",$name)){
            return false;
        }

        if(strlen($name) > S2Base_Request::MAX_LEN){
            return false;
        }
        
        return true;
    }
}
?>
