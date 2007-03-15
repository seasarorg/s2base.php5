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
class S2Base_ZfDefaultView
    extends Zend_View
    implements S2Base_ZfView {

    private $template = null;
    private $request = null;
    private $response = null;
    private $scriptPath = null;
    
    public function __construct(){
        parent::__construct();
        $this->request = Zend_Controller_Front::getInstance()->getRequest();
        $this->response = Zend_Controller_Front::getInstance()->getResponse();
    }
    
    public function setTpl($tpl) {
        $this->template = $tpl;
    }

    public function getTpl() {
        return $this->template;
    }

    public function setScriptPath($tpl) {
        $this->template = $tpl;
    }

    public function renderWithTpl() {
        $this->addScriptPath(S2BASE_PHP5_ROOT
                           . '/app/modules/'
                           . S2Base_ZfDispatcherSupportPlugin::getModuleName($this->request) . '/'
                           . $this->request->getControllerName()
                           . '/view');
        if ($this->template == null) {
            $this->response->setBody($this->render(
                   $this->request->getActionName() . S2BASE_PHP5_ZF_TPL_SUFFIX));
        } else {
            $this->response->setBody($this->render($this->template));
        }
    }
}
?>
