
    public function @@ACTION_METHOD_NAME@@() {
        $this->_view->assign('func','create');
        $this->_view->assign('dto',new @@ENTITY_CLASS_NAME@@());
        $this->_view->setTpl('@@ACTION_NAME@@-input.html');
    }
    /** S2BASE_PHP5 ACTION METHOD **/