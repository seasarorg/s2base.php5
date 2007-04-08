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
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2006 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.0.2
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.2
 * @package    org.seasar.s2base.zf.view.impl
 * @author     klove
 */
class S2Base_ZfSmartyView
    extends Smarty
    implements Zend_View_Interface, S2Base_ZfView {

    public  static $config   = array();
    private static $rendered = false;
    private static $errors   = array();
    private $layout     = null;
    private $scriptPath = null;
    private $request    = null;
    private $response   = null;
    private $template   = null;

    public function __construct(){
        parent::__construct();
        foreach(self::$config as $key=>$val){
            $this->$key = $val;
        }

        if(defined('S2BASE_PHP5_LAYOUT')){
            $this->layout = S2BASE_PHP5_LAYOUT;
        }

        $this->request = Zend_Controller_Front::getInstance()->getRequest();
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
        $this->setScriptPath(S2BASE_PHP5_ROOT . '/app/modules/');
    }

    public static function setRendered($value = true){
        self::$rendered = $value;
    }

    public static function isRendered(){
        return self::$rendered;
    }

    public function setTpl($tpl) {
        $this->template = $tpl;
    }

    public function getTpl() {
        return $this->template;
    }

    public function setLayout($layout){
        $this->layout = $layout;
    }

    public function putError($key,$val){
        self::$errors[$key] = $val;
    }

    public function getError($key){
        if(isset(self::$errors[$key])){
            return self::$errors[$key];
        }
        return null;
    }

    public function getErrors(){
        return self::$errors;
    }

    public function setRequest(Zend_Controller_Request_Abstract $request){
        $this->request = $request;
    }

    public function setResponse(Zend_Controller_Response_Abstract $response){
        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }

    public function getEngine() {
        return $this;
    }

    public function setScriptPath($path) {
        $this->scriptPath = $path;
    }

    /**
     * @see Zend_View_Interface::__set()
     */
    public function __set($key, $val){}

    /**
     * @see Zend_View_Interface::__get()
     */
    public function __get($key){}

    /**
     * @see Zend_View_Interface::__isset()
     */
    public function __isset($key){}

    /**
     * @see Zend_View_Interface::__unset()
     */
    public function __unset($key){}

    /**
     * @see Zend_View_Interface::__clearVars()
     */
    public function clearVars(){}

    /**
     * @see Zend_View_Interface::render()
     */
    public function render($script) {
        if (self::isRendered()) {
            return;
        }
        self::setRendered();

        $this->putError('validate', S2Base_ZfValidateSupportPlugin::getErrors($this->request));
        $moduleName = $this->request->getModuleName();
        $this->template_dir = $this->scriptPath;
        $this->assign('request',$this->request);
        $this->assign('errors',self::$errors);
        $this->assign('module', $moduleName);
        $this->assign('controller', $this->request->getControllerName());
        $this->assign('action', $this->request->getActionName());
        $this->assign('base_url', $this->request->getBaseUrl());
        $mod_url = $moduleName === S2BASE_PHP5_ZF_DEFAULT_MODULE ?
                   $this->request->getBaseUrl() :
                   $this->request->getBaseUrl() . '/' . $moduleName;
        $ctl_url = $mod_url . '/' . $this->request->getControllerName();
        $act_url = $ctl_url . '/' . $this->request->getActionName();
        $this->assign('mod_url', $mod_url);
        $this->assign('ctl_url', $ctl_url);
        $this->assign('act_url', $act_url);
        $ctlViewDir = $moduleName
                    . DIRECTORY_SEPARATOR
                    . $this->request->getControllerName()
                    . DIRECTORY_SEPARATOR
                    . 'view';
        $this->assign('ctl_view_dir', $this->scriptPath . DIRECTORY_SEPARATOR . $ctlViewDir);
        $this->assign('commons_view_dir', S2BASE_PHP5_ROOT . '/app/commons/view');

        if ($this->template === null) {
            $viewFile = $ctlViewDir . DIRECTORY_SEPARATOR . $script;
            if (!file_exists($this->template_dir . DIRECTORY_SEPARATOR . $viewFile)) {
                throw new S2Base_ZfException('template file not found. [' . 
                     $this->template_dir . DIRECTORY_SEPARATOR . $viewFile . ']');
            }
        } else {
            if (preg_match('/^file:/',$this->template)){
                $viewFile = $this->template;
            } else {
                if (!preg_match('/\.' . S2BASE_PHP5_ZF_TPL_SUFFIX . '$/', $this->template)) {
                    $this->template .= '.' . S2BASE_PHP5_ZF_TPL_SUFFIX;
                }
                $viewFile = $ctlViewDir . DIRECTORY_SEPARATOR . $this->template;
                if (!file_exists($this->template_dir . DIRECTORY_SEPARATOR . $viewFile)) {
                    throw new S2Base_ZfException('template file not found. [' . 
                         $this->template_dir . DIRECTORY_SEPARATOR . $viewFile . ']');
                }
            }
        }

        if($this->layout === null){
            return $this->fetch($viewFile);
        }else{
            $this->assign('content_for_layout', $viewFile);
            return $this->fetch($this->layout);
        }
    }
}
?>
