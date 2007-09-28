
    public function @@ACTION_METHOD_NAME@@() {
        if (S2Base_ZfValidateSupportPlugin::hasError($this->getRequest())) {
            $dto = $this->create@@ENTITY_CLASS_NAME@@FromRequest($this->getRequest());
        } else {
            $dto = new @@ENTITY_CLASS_NAME@@();
        }
        $this->view->assign('func', 'create');
        $this->view->assign('dto', $dto);
        $this->_helper->viewRenderer('@@ACTION_NAME@@-input');
    }
    /** S2BASE_PHP5 ACTION METHOD **/