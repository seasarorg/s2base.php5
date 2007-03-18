
    public function @@ACTION_METHOD_NAME@@() {
        if (S2Base_ZfValidateSupportPlugin::hasError($this->getRequest())) {
            $dto = $this->createScaffoldEntityFromRequest($this->getRequest());
        } else {
            $dto = new @@ENTITY_CLASS_NAME@@();
        }
        $this->view->assign('func', 'create');
        $this->view->assign('dto', $dto);
        $this->view->setTpl('@@ACTION_NAME@@-input.html');
    }
    /** S2BASE_PHP5 ACTION METHOD **/