
    public function @@ACTION_METHOD_NAME@@() {
        $func = $this->getRequest()->getParam('func');
        $dto = $this->_helper->FlashMessenger->getMessages();
        if (count($dto) !== 1 and !is_array($dto[0])) {
            throw new Exception('invalid session data found.');
        }
        switch ($func) {
            case 'create':
                $this->@@MODEL_PROPERTY@@->insert($dto[0]);
                break;
            case 'update':
                $this->@@MODEL_PROPERTY@@->updateById($dto[0]);
                break;
            case 'delete':
                $this->@@MODEL_PROPERTY@@->deleteById($dto[0]);
                break;
        }
        $this->_helper->Redirector->goto('@@ACTION_NAME@@');
    }
    /** S2BASE_PHP5 ACTION METHOD **/