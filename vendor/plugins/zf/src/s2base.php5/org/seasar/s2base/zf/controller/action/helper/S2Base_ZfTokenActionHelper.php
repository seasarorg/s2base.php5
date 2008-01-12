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
    /**
     * @var string
     */
    public static $SESSION_TOKEN_KEY  = 'token';

    /**
     * @var string
     */
    public static $REQUEST_TOKEN_KEY  = 'token';

    /**
     * @var string
     */
    public static $TOKEN_NAMESPACE    = __CLASS__;

    /**
     * @var string
     */
    private $moduleName     = null;

    /**
     * @var string
     */
    private $controllerName = null;

    /**
     * @var string
     */
    private $actionName     = null;

    /**
     * @var boolean
     */
    private $onetime        = true;

    /**
     * @var Zend_Session_Namespace
     */
    private $session        = null;

    /**
     * @var boolean
     */
    private $autoCheck      = true;

    /**
     * @var boolean
     */
    private $autoAsign      = true;

    /**
     * @see Zend_Controller_Action_Helper_Abstract::getName()
     */
    public function getName() {
        return 'TokenHelper';
    }

    /**
     * リクエストメソッドが POST で、自動確認設定が成されている場合にTokenを確認します。
     * @see Zend_Controller_Action_Helper_Abstract::preDispatch()
     */
    public function preDispatch() {
        if (!$this->getRequest()->isPost()) {
            return;
        }

        if ($this->autoCheck) {
            $this->check();
        }
    }


    /**
     * 自動アサイン設定が成されている場合に、Tokenをアサインします。
     * @see Zend_Controller_Action_Helper_Abstract::postDispatch()
     */
    public function postDispatch() {
        if ($this->autoAsign) {
            $this->asign();
        }
    }

    /**
     * リクエストパラメータまたはセッションにTokenが存在した場合に比較確認を行います。
     * リクエストパラメータとセッションのTokenが不一致の場合、アクション名が指定されていれば、
     * 指定されたアクション名をリクエストに設定してディスパッチをループします。
     * アクション名が指定されていない場合は、例外をスローします。
     * @throw S2Base_ZfException
     */
    public function check() {
        $request    = $this->getRequest();
        $session    = $this->getSession();
        $sessionKey = self::$SESSION_TOKEN_KEY;

        if ($request->has(self::$REQUEST_TOKEN_KEY) or isset($this->getSession()->$sessionKey) ) {
            if ($request->getParam(self::$REQUEST_TOKEN_KEY) === $this->getSession()->$sessionKey) {
                Zend_Registry::get('logger')->debug('token checked.');
                if ($this->onetime) {
                    $this->unsetSession();
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
     * レスポンスに格納されているHTMLにformタグが存在した場合にTokenを埋め込みます。
     */
    public function asign() {
        $sessionKey = self::$SESSION_TOKEN_KEY;
        $bodies = $this->getResponse()->getBody(true);
        if ($this->onetime or !isset($this->getSession()->$sessionKey)) {
            $token = $this->generate();
        } else {
            $token = $this->getSession()->$sessionKey;
        }
        $isUpdated = false;
        $pattern = '<form\s.*?method="POST".*?>';
        foreach ($bodies as $name => $content) {
            if (preg_match('/(' . $pattern . ')/isu', $content)) {
                $isUpdated = true;
                $replacement = '$1<input type="hidden" name="' . self::$REQUEST_TOKEN_KEY . '" value="' . $token . '" />';
                $this->getResponse()->setBody(preg_replace('/(' . $pattern . ')/isu', $replacement, $content), $name);
            }
        }
        if ($isUpdated) {
            $this->getSession()->$sessionKey = $token;
        }
    }

    /**
     * @return string
     */
    public function generate() {
        return sha1(uniqid(rand(), true));
    }

    /**
     * @param string $moduleName
     */
    public function setModuleName($moduleName) {
        $this->moduleName = $moduleName;
    }

    /**
     * @param string $controllerName
     */
    public function setControllerName($controllerName) {
        $this->controllerName = $controllerName;
    }

    /**
     * @param string $actionName
     */
    public function setActionName($actionName) {
        $this->actionName = $actionName;
    }

    /**
     * @param boolean $value
     */
    public function setOnetime($value = true) {
        $this->onetime = $value;
    }

    /**
     * @param boolean $value
     */
    public function setAutoCheck($value = true) {
        $this->autoCheck = $value;
    }

    /**
     * @param boolean $value
     */
    public function setAutoAsign($value = true) {
        $this->autoAsign = $value;
    }

    /**
     * 使用したセッションをクリアします。
     */
    public function unsetSession() {
        Zend_Session::namespaceUnset(self::$TOKEN_NAMESPACE);
    }

    /**
     * @return Zend_Session_Namespace
     */
    private function getSession() {
        if ($this->session === null) {
            $this->session = new Zend_Session_Namespace(self::$TOKEN_NAMESPACE);
        }
        return $this->session;
    }
}
