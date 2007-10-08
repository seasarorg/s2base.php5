
    public function @@ACTION_METHOD_NAME@@() {
        if (S2Base_ZfValidateSupportPlugin::hasError($this->getRequest())) {
            $row = $this->create@@MODEL_CLASS@@RowFromRequest($this->getRequest());
        } else {
            $row = $this->@@MODEL_PROPERTY@@->createRow();
        }
        $this->view->assign('func', 'create');
        $this->view->assign('dto', $row);
        $this->_helper->viewRenderer('@@ACTION_NAME@@-input');
    }
    /** S2BASE_PHP5 ACTION METHOD **/