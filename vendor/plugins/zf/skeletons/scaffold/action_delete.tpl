
    public function @@ACTION_METHOD_NAME@@() {
        $pk = $this->getRequest()->getParam('@@UNIQUE_KEY_NAME@@');
        $dto = $this->@@SERVICE_PROPERTY@@->getById($pk);
        if (count($dto) == 0) {
            throw new Exception("primarty key not found.[$pk]");
        }
        $this->view->assign('dto', $dto);
        $this->view->assign('func', 'delete');

        $this->_helper->FlashMessenger->addMessage($dto);
        $this->_helper->viewRenderer('@@ACTION_NAME@@-confirm');
    }
    /** S2BASE_PHP5 ACTION METHOD **/