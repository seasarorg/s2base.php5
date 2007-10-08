
    public function create@@MODEL_CLASS@@RowFromRequest($request) {
        $row = $this->@@MODEL_PROPERTY@@->createRow();
        @@CREATE_DTO_METHOD@@
        return $row;
    }

    public function @@ACTION_METHOD_NAME@@() {
        $this->view->assign('func', $this->_request->getParam('func'));
        $row = $this->create@@MODEL_CLASS@@RowFromRequest($this->getRequest());
        $this->view->assign('dto', $row);
        $this->_helper->FlashMessenger->addMessage($row->toArray());
        if (S2Base_ZfValidateSupportPlugin::hasError($this->_request)) {
            $this->_helper->viewRenderer('@@ACTION_NAME@@-input');
        }
    }
    /** S2BASE_PHP5 ACTION METHOD **/