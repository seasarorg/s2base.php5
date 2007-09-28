<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action {
    public function indexAction() {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->view->assign('identity', Zend_Auth::getInstance()->getIdentity());
        } else {
            $this->_helper->Redirector->goto('login');
        }
    }
    public function loginAction() {
        $message = '';
        if (S2Base_ZfAnaSupportPlugin::hasError($this->_request)) {
            $error = S2Base_ZfAnaSupportPlugin::getError($this->_request);
            $message = $error['message'];
        }
            $this->_helper->ViewRenderer->putError('ana', $message);
    }
    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->Redirector->goto('index');
    }
    public function anaAction() {
        $auth = Zend_Auth::getInstance();
        $authAdapter = new IniPasswdAuthAdapter($this->_request->getParam('identity'),
                                                $this->_request->getParam('credential'));
        /*
        require_once('Zend/Auth/Adapter/DbTable.php');
        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter(), 'USERS', 'USERNAME', 'PASSWORD');
        $authAdapter->setIdentity($this->_request->getParam('identity'))
                    ->setCredential($this->_request->getParam('credential'));
        */
        $result = $auth->authenticate($authAdapter);
        if ($result->getCode() === Zend_Auth_Result::SUCCESS) {
            $this->_helper->Redirector->goto('index');
        } else {
            $this->_helper->ViewRenderer->putError('ana', $result->getMessages());
            $this->_helper->ViewRenderer('login');
        }
    }
    /** S2BASE_PHP5 ACTION METHOD **/

    public function __call($methodName, $args) {
        return parent::__call($methodName, $args);
    }
}

class IniPasswdAuthAdapter implements Zend_Auth_Adapter_Interface {
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
