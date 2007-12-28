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
 * @version    Release: 2.0.0
 * @link       http://s2base.php5.seasar.org/
 * @since      Class available since Release 2.0.0
 * @package    org.seasar.s2base.zf.ana
 * @author     klove
 */
class S2Base_ZfIniPasswdAuthAdapter implements Zend_Auth_Adapter_Interface {
    private $identity = null;
    private $credential = null;
    private $passwdIniFile = null;
    public function __construct($identity = null, $credential = null) {
        $this->identity = $identity;
        $this->credential = $credential;
        if (defined('S2BASE_PHP5_ROOT')) {
            $this->passwdIniFile = S2BASE_PHP5_ROOT . '/config/passwd.ini';
        }
    }
    public function setPasswdIniFile($value) {
        $this->passwdIniFile = $value;
        return $this;
    }
    public function setIdentity($value) {
        $this->identity = $value;
        return $this;
    }
    public function setCredential($credential) {
        $this->credential = $credential;
        return $this;
    }
    public function authenticate() {
        $authResult = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->identity,
            'messages' => array()
            );
        $configs = new Zend_Config_Ini($this->passwdIniFile, null);
        $isAuthed = false;
        while ($configs->valid()) {
            $config = $configs->current();
            if (isset($config->user) and isset($config->passwd)) {
                if ($config->user === $this->identity) {
                    if ($config->passwd === $this->credential) {
                        $authResult['code'] = Zend_Auth_Result::SUCCESS;
                        $authResult['messages'][] = 'Authentication successful.';
                    } else {
                        $authResult['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
                        $authResult['messages'][] = 'Supplied credential is invalid.';
                    }
                    $isAuthed = true;
                    break;
                }
            } else {
                throw new Exception("invalid passwd data found.[id : {$configs->key()}]");
            }
            $configs->next();
        }

        if (!$isAuthed) {
            $authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $authResult['messages'][] = 'A record with the supplied identity could not be found.';
        }
        return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
    }
}
