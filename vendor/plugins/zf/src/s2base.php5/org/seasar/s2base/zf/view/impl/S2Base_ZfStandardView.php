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
class S2Base_ZfStandardView
    extends Zend_View
    implements S2Base_ZfView {

    protected static $errors   = array();
    protected $layout     = null;
    protected $request    = null;
    protected $response   = null;
    protected $template   = null;

    public function __construct(){
        parent::__construct(array(
            'scriptPath' => S2BASE_PHP5_ROOT . '/app/commons/view',
            'helperPath' => S2BASE_PHP5_ROOT . '/app/commons/view/helpers',
            'filterPath' => S2BASE_PHP5_ROOT . '/app/commons/view/filters'
        ));

        $this->request = Zend_Controller_Front::getInstance()->getRequest();
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
        if(defined('S2BASE_PHP5_LAYOUT')){
            $this->layout = S2BASE_PHP5_LAYOUT;
        }
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

    public function render($script) {
        $ctlViewDir = S2BASE_PHP5_ROOT . '/app/modules/'
                    . S2Base_ZfDispatcherSupportPlugin::getModuleName($this->request)
                    . '/' . $this->request->getControllerName() . '/view';
        $this->addScriptPath($ctlViewDir);
        $this->addHelperPath($ctlViewDir . '/helpers');
        $this->addFilterPath($ctlViewDir . '/filters');

        $this->putError('validate', S2Base_ZfValidateSupportPlugin::getErrors($this->request));
        $moduleName = S2Base_ZfDispatcherSupportPlugin::getModuleName($this->request);
        $this->assign('request', $this->request);
        $this->assign('errors', self::$errors);
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
            $viewFile = $script;
        } else {
            $viewFile = $this->template;
        }

        if($this->layout === null){
            return parent::render($viewFile);
        }else{
            $this->assign('content_for_layout', $viewFile);
            return parent::render($this->layout);
        }
    }
}
?>
