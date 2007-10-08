
    public function @@ACTION_METHOD_NAME@@() {
        $pk = $this->getRequest()->getParam('@@UNIQUE_KEY_NAME@@');
        $rowset = $this->@@MODEL_PROPERTY@@->find($pk);
        if (count($rowset) == 0) {
            throw new Exception("primarty key not found.[$pk]");
        }
        $this->view->assign('dto', $rowset->current());
        $this->view->assign('func', 'delete');

        $this->_helper->FlashMessenger->addMessage($rowset->current()->toArray());
        $this->_helper->viewRenderer('@@ACTION_NAME@@-confirm');
    }
    /** S2BASE_PHP5 ACTION METHOD **/