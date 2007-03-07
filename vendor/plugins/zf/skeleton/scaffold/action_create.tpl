
    public function @@ACTION_METHOD_NAME@@() {
        if ($this->getRequest()->has(S2Base_ZfValidatorSupportPlugin::ERRORS_KEY)) {
            $dto = $this->createScaffoldEntityFromRequest($this->getRequest());
        } else {
            $dto = new @@ENTITY_CLASS_NAME@@();
        }
        $this->_view->assign('func', 'create');
        $this->_view->assign('dto', $dto);
        $this->_view->setTpl('@@ACTION_NAME@@-input.html');
    }
    /** S2BASE_PHP5 ACTION METHOD **/