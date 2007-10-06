
    public function create@@ENTITY_CLASS_NAME@@FromRequest($request) {
        $dto = new @@ENTITY_CLASS_NAME@@();
        @@CREATE_DTO_METHOD@@
        return $dto;
    }

    public function @@ACTION_METHOD_NAME@@() {
        $this->view->assign('func', $this->getRequest()->getParam('func'));
        $dto = $this->create@@ENTITY_CLASS_NAME@@FromRequest($this->getRequest());
        $this->view->assign('dto', $dto);
        $this->_helper->FlashMessenger->addMessage($dto);
        if (S2Base_ZfValidateSupportPlugin::hasError($this->getRequest())) {
            $this->_helper->viewRenderer('@@ACTION_NAME@@-input');
        }
    }
    /** S2BASE_PHP5 ACTION METHOD **/