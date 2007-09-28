
    public function @@ACTION_METHOD_NAME@@() {
        $func = $this->getRequest()->getParam('func');
        $dto = $this->_helper->FlashMessenger->getMessages();
        if (count($dto) == 1 and $dto[0] instanceof @@ENTITY_CLASS_NAME@@) {
            $dto = $dto[0];
        } else {
            throw new Exception('invalid session data found.');
        }
        switch ($func) {
            case 'create':
                $this->@@SERVICE_PROPERTY@@->createByDto($dto);
                break;
            case 'update':
                $this->@@SERVICE_PROPERTY@@->updateByDto($dto);
                break;
            case 'delete':
                $this->@@SERVICE_PROPERTY@@->deleteByDto($dto);
                break;
        }
        $this->_helper->Redirector->goto('@@ACTION_NAME@@');
    }
    /** S2BASE_PHP5 ACTION METHOD **/