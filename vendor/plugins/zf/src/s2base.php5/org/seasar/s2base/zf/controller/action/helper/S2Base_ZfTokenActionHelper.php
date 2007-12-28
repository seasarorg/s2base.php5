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
// $Id:$
/**
 * S2Base.PHP5 with Zf
 * 
 * @copyright  2005-2007 the Seasar Foundation and the Others.
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    Release: 1.2.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 1.0.2
 * @package    org.seasar.s2base.zf.controller.action.helper
 * @author     klove
 */
class S2Base_ZfTokenActionHelper extends Zend_Controller_Action_Helper_Abstract {
    public static $SESSION_TOKEN_KEY  = 'token';
    public static $REQUEST_TOKEN_KEY  = 'token';
    public static $TOKEN_NAMESPACE    = __CLASS__;

    private $moduleName     = null;
    private $controllerName = null;
    private $actionName     = null;
    private $onetime        = true;
    private $session        = null;
    private $autoCheck      = true;
    private $autoAsign      = true;

    /**
     * @see Zend_Controller_Action_Helper_Abstract::getName()
     */
    public function getName() {
        return 'TokenHelper';
    }

    /**
     * @see Zend_Controller_Action_Helper_Abstract::preDispatch()
     */
    public function preDispatch() {
        if (strtolower($this->getRequest()->getMethod()) !== 'post') {
            return;
        }

        if ($this->autoCheck) {
            $this->check();
        }
    }

    public function check() {
        $request    = $this->getRequest();
        $session    = $this->getSession();
        $sessionKey = self::$SESSION_TOKEN_KEY;

        if ($request->has(self::$REQUEST_TOKEN_KEY) or isset($this->getSession()->$sessionKey) ) {
            if ($request->getParam(self::$REQUEST_TOKEN_KEY) === $this->getSession()->$sessionKey) {
                Zend_Registry::get('logger')->debug('token checked.');
                if ($this->onetime) {
                    Zend_Session::namespaceUnset(self::$TOKEN_NAMESPACE);
                }
                return;
            }
        } else {
            Zend_Registry::get('logger')->warn('token not checked. request nor session token found.');
            return;
        }

        if ($this->actionName === null) {
            throw new S2Base_ZfException('token unmatch');
        } else {
            if ($this->moduleName !== null) {
                $request->setModuleName($this->moduleName);
            }
            if ($this->controllerName !== null) {
                $request->setControllerName($this->controllerName);
            }
            $request->setActionName($this->actionName)
                    ->setDispatched(false);
            Zend_Registry::get('logger')->warn('token unmatch.');
        }
    }

    /**
     * @see Zend_Controller_Action_Helper_Abstract::postDispatch()
     */
    public function postDispatch() {
        if ($this->autoAsign) {
            $this->asign();
        }
    }

    public function asign() {
        $sessionKey = self::$SESSION_TOKEN_KEY;
        $bodies = $this->getResponse()->getBody(true);
        if ($this->onetime or !isset($this->getSession()->$sessionKey)) {
            $token  = $this->generate();
        } else {
            $token = $this->getSession()->$sessionKey;
        }
        $updated = false;
        $pattern = '<form\s.*?method="POST".*?>';
        foreach ($bodies as $name => $content) {
            if (preg_match('/(' . $pattern . ')/isu', $content)) {
                $updated = true;
                $replacement = '$1<input type="hidden" name="' . self::$REQUEST_TOKEN_KEY . '" value="' . $token . '" />';
                $this->getResponse()->setBody(preg_replace('/(' . $pattern . ')/isu', $replacement, $content), $name);
            }
        }

        if ($updated) {
            $this->getSession()->$sessionKey = $token;
        }
    }

    private function getSession() {
        if ($this->session === null) {
            $this->session = new Zend_Session_Namespace(self::$TOKEN_NAMESPACE);
        }
        return $this->session;
    }

    public function generate() {
        return sha1(uniqid(rand(), true));
    }

    public function setActionName($actionName) {
        $this->actionName = $actionName;
    }

    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    public function setModuleName($moduleName) {
        $this->moduleName = $moduleName;
    }

    public function setOnetime($value = true) {
        $this->onetime = $value;
    }

    public function setAutoCheck($value = true) {
        $this->autoCheck = $value;
    }

    public function setAutoAsign($value = true) {
        $this->autoAsign = $value;
    }
}
