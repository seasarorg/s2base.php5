
    public function @@ACTION_METHOD_NAME@@() {
        $func = $this->getRequest()->getParam('func');
        $sn = new Zend_Session_Namespace('action_@@ACTION_NAME@@');
        $dto = $sn->@@DTO_SESSION_KEY@@;
        switch ($func) {
            case 'create':
                $this->service->createByDto($dto);
                break;
            case 'update':
                $this->service->updateByDto($dto);
                break;
            case 'delete':
                $this->service->deleteByDto($dto);
                break;
        }
        unset($_SESSION['@@DTO_SESSION_KEY@@']);
        $url = $this->getRequest()->getModuleName() === S2BASE_PHP5_ZF_DEFAULT_MODULE ? '' :
               '/' . $this->getRequest()->getModuleName();
        $url .= "/{$this->getRequest()->getControllerName()}/@@ACTION_NAME@@";
        $this->_redirect($url);
    }
    /** S2BASE_PHP5 ACTION METHOD **/