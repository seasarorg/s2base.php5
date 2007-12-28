<?php
class @@CONTROLLER_CLASS_NAME@@ extends Zend_Controller_Action {
    public function indexAction() {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $session = new Zend_Session_Namespace(__CLASS__);
            if (isset($session->error)) {
                $error = $session->error;
                Zend_Session::namespaceUnset(__CLASS__);
                $this->_helper->Redirector->goto($error['action'], $error['controller'], $error['module']);
            } else {
                $this->view->assign('identity', Zend_Auth::getInstance()->getIdentity());
            }
        } else {
            $this->_helper->Redirector->goto('login');
        }
    }

    public function loginAction() {
        $message = '';
        if ($this->_helper->AnaHelper->hasError()) {
            $error = $this->_helper->AnaHelper->getError();
            $message = $error['message'];
            $session = new Zend_Session_Namespace(__CLASS__);
            $session->error = $error;
        }
        $this->_helper->ViewRenderer->putError('ana', $message);
    }

    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->Redirector->goto('index');
    }

    public function anaAction() {
        $auth = Zend_Auth::getInstance();
        $authAdapter = new S2Base_ZfIniPasswdAuthAdapter($this->_request->getParam('identity'),
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
